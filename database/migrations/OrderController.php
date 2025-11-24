<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderPin;
use App\Models\Product;
use App\Models\ProductCredential;
use App\Models\ProductDetail;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Mail\OrderConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $orders = auth()->user()->orders()
            ->with(['product', 'credential', 'pin'])
            ->latest()
            ->paginate(15);

        return view('orders.index', compact('orders'));
    }

    public function store(Request $request, Product $product)
    {
        $request->validate([
            'payment_method' => 'required|in:wallet,paystack,stripe,razorpay,payvibe,btcpay,coingate',
        ]);

        if (!$product->is_active || $product->available_stock < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Product is currently unavailable.',
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Lock first unsold ProductDetail (TXT-based inventory)
            $productDetail = ProductDetail::where('product_id', $product->id)
                ->where('is_sold', false)
                ->lockForUpdate()
                ->first();

            if (!$productDetail) {
                // Fallback to old ProductCredential system
                $credential = ProductCredential::where('product_id', $product->id)
                    ->where('is_sold', false)
                    ->lockForUpdate()
                    ->first();

                if (!$credential) {
                    throw new \Exception('No available stock for this product.');
                }
            } else {
                $credential = null; // Using ProductDetail
            }

            // Handle payment based on method
            $transaction = null;
            if ($request->payment_method === 'wallet') {
                $wallet = auth()->user()->wallet;
                if (!$wallet || $wallet->balance < $product->price) {
                    throw new \Exception('Insufficient wallet balance.');
                }

                $wallet->decrement('balance', $product->price);
                $transaction = Transaction::create([
                    'user_id' => auth()->id(),
                    'wallet_id' => $wallet->id,
                    'type' => 'purchase',
                    'amount' => $product->price,
                    'status' => 'completed',
                    'reference' => 'PUR-' . time() . rand(1000, 9999),
                    'description' => "Purchase: {$product->name}",
                ]);
            } else {
                // External gateway payment - create pending transaction
                $transaction = Transaction::create([
                    'user_id' => auth()->id(),
                    'type' => 'purchase',
                    'amount' => $product->price,
                    'gateway' => $request->payment_method,
                    'status' => 'pending',
                    'reference' => 'PUR-' . time() . rand(1000, 9999),
                    'description' => "Purchase: {$product->name}",
                ]);

                // Here you would redirect to payment gateway
                // For now, we'll mark it as completed for demo
                // In production, you'd handle webhooks
            }

            // Create order
            $order = Order::create([
                'user_id' => auth()->id(),
                'product_id' => $product->id,
                'credential_id' => $credential ? $credential->id : null,
                'product_detail_id' => $productDetail ? $productDetail->id : null,
                'amount' => $product->price,
                'status' => $request->payment_method === 'wallet' ? 'paid' : 'pending',
                'payment_method' => $request->payment_method,
                'transaction_id' => $transaction->id,
            ]);

            // Mark as sold (ProductDetail or ProductCredential)
            if ($productDetail) {
                $productDetail->update(['is_sold' => true]);
            } else {
                $credential->update([
                    'is_sold' => true,
                    'sold_to_order_id' => $order->id,
                ]);
            }

            // Generate PIN for order
            $pin = OrderPin::generatePin();
            OrderPin::create([
                'order_id' => $order->id,
                'pin' => $pin,
            ]);

            // Update order status if wallet payment
            if ($request->payment_method === 'wallet') {
                $order->update(['status' => 'delivered']);
            }

            DB::commit();

            // Send confirmation email
            try {
                Mail::to(auth()->user()->email)->send(new OrderConfirmation($order));
            } catch (\Exception $e) {
                // Log error but don't fail order
                \Log::error('Failed to send order confirmation email: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully! A confirmation email has been sent to your inbox.',
                'order_id' => $order->id,
                'redirect' => route('orders.show', $order),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id() && !auth()->user()->is_admin) {
            abort(403);
        }

        $order->load(['product', 'credential', 'productDetail', 'pin', 'transaction']);

        return view('orders.show', compact('order'));
    }

    public function revealCredentials(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'pin' => 'required|digits_between:4,6',
        ]);

        $user = auth()->user();
        $userPinOk = (!empty($user->pin_hash)) ? \Illuminate\Support\Facades\Hash::check($request->pin, $user->pin_hash) : false;

        if (!$userPinOk) {
            // Fallback to order-specific PIN
            if (!$order->pin) {
                return response()->json([
                    'success' => false,
                    'message' => 'PIN not found for this order.',
                ], 404);
            }

            if ($order->pin->is_used) {
                return response()->json([
                    'success' => false,
                    'message' => 'PIN has already been used.',
                ], 400);
            }

            if (!$order->pin->verifyPin($request->pin)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid PIN. Please try again.',
                ], 401);
            }

            // Mark only order PIN as used when we validated via order pin
            $order->pin->markAsUsed();
        }

        // Update order status
        if ($order->status === 'delivered') {
            $order->update(['status' => 'completed']);
        }

        // Load credential details
        $order->load(['credential', 'productDetail']);

        // If using new ProductDetail system, return details
        if ($order->productDetail) {
            return response()->json([
                'success' => true,
                'message' => 'Product details revealed successfully!',
                'details' => $order->productDetail->details,
            ]);
        }

        // Otherwise return ProductCredential fields
        return response()->json([
            'success' => true,
            'message' => 'Credentials revealed successfully!',
            'credentials' => [
                'username' => $order->credential->username ?? null,
                'password' => $order->credential->password ?? null,
                'email' => $order->credential->email ?? null,
                'authenticator_code' => $order->credential->authenticator_code ?? null,
                'authenticator_site' => $order->credential->authenticator_site ?? null,
                'additional_info' => $order->credential->additional_info ?? null,
            ],
        ]);
    }

    public function requestReplacement(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($order->is_replaced || $order->has_replacement_request) {
            return response()->json([
                'success' => false,
                'message' => 'Replacement already requested or completed.',
            ], 400);
        }

        $order->update(['has_replacement_request' => true]);

        // Create ticket for replacement
        \App\Models\Ticket::create([
            'user_id' => auth()->id(),
            'order_id' => $order->id,
            'subject' => "Replacement Request for Order #{$order->order_number}",
            'message' => $request->message ?? 'Please replace this log as it is invalid.',
            'type' => 'replacement',
            'is_replacement_request' => true,
            'priority' => 'high',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Replacement request submitted successfully! We\'ve got your back, your replacement is on the way!',
        ]);
    }
}



