<?php

namespace App\Http\Controllers;

use App\Models\ManualPayment;
use App\Models\BankingDetail;
use App\Models\Order;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ManualPaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showForm(Request $request, Product $product = null)
    {
        $bankingDetails = BankingDetail::active()->get();
        
        if (!$bankingDetails->count()) {
            return redirect()->back()->with('error', 'Manual payment is currently unavailable. No banking details configured.');
        }

        $amount = $request->amount ?? ($product ? $product->price : 0);
        $orderId = $request->order_id ?? null;

        return view('manual-payment.form', compact('bankingDetails', 'amount', 'product', 'orderId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'banking_detail_id' => 'required|exists:banking_details,id',
            'receipt' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
            'notes' => 'nullable|string|max:1000',
            'order_id' => 'nullable|exists:orders,id',
            'product_id' => 'nullable|exists:products,id',
        ]);

        // Check if banking detail is active
        $bankingDetail = BankingDetail::findOrFail($request->banking_detail_id);
        if (!$bankingDetail->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Selected banking detail is not available.',
            ], 400);
        }

        // Handle receipt upload
        $receiptPath = $request->file('receipt')->store('receipts', 'public');

        // Generate unique reference
        $reference = 'MAN-' . strtoupper(Str::random(8)) . '-' . time();

        // Create manual payment
        $manualPayment = ManualPayment::create([
            'user_id' => auth()->id(),
            'order_id' => $request->order_id,
            'banking_detail_id' => $request->banking_detail_id,
            'amount' => $request->amount,
            'reference' => $reference,
            'status' => 'pending',
            'receipt_path' => $receiptPath,
            'notes' => $request->notes,
        ]);

        // If this is for an order, create pending transaction
        if ($request->order_id) {
            $order = Order::findOrFail($request->order_id);
            $transaction = Transaction::create([
                'user_id' => auth()->id(),
                'type' => 'purchase',
                'amount' => $request->amount,
                'gateway' => 'manual',
                'status' => 'pending',
                'reference' => $reference,
                'description' => 'Manual payment for order #' . $order->order_number,
            ]);

            $manualPayment->update(['transaction_id' => $transaction->id]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment receipt uploaded successfully! Your payment is pending admin approval.',
            'manual_payment_id' => $manualPayment->id,
            'redirect' => route('manual-payment.show', $manualPayment),
        ]);
    }

    public function show(ManualPayment $manualPayment)
    {
        if ($manualPayment->user_id !== auth()->id() && !auth()->user()->is_admin) {
            abort(403);
        }

        $manualPayment->load(['bankingDetail', 'order', 'approver']);

        return view('manual-payment.show', compact('manualPayment'));
    }

    public function index()
    {
        $manualPayments = auth()->user()->manualPayments()
            ->with(['bankingDetail', 'order', 'approver'])
            ->latest()
            ->paginate(15);

        return view('manual-payment.index', compact('manualPayments'));
    }
}

