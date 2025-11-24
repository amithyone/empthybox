<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ManualPayment;
use App\Models\BankingDetail;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManualPaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->is_admin) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = ManualPayment::with(['user', 'bankingDetail', 'order', 'approver']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'pending'); // Default to pending
        }

        // Search by reference
        if ($request->filled('reference')) {
            $query->where('reference', 'like', '%' . $request->reference . '%');
        }

        // Search by user email
        if ($request->filled('email')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('email', 'like', '%' . $request->email . '%');
            });
        }

        $manualPayments = $query->latest()->paginate(20);
        $pendingCount = ManualPayment::where('status', 'pending')->count();

        return view('admin.manual-payments.index', compact('manualPayments', 'pendingCount'));
    }

    public function show(ManualPayment $manualPayment)
    {
        $manualPayment->load(['user', 'bankingDetail', 'order', 'approver', 'transaction']);

        return view('admin.manual-payments.show', compact('manualPayment'));
    }

    public function approve(Request $request, ManualPayment $manualPayment)
    {
        if ($manualPayment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This payment has already been processed.',
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Update manual payment
            $manualPayment->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'admin_notes' => $request->admin_notes,
            ]);

            // If this is for an order, complete the order
            if ($manualPayment->order_id) {
                $order = $manualPayment->order;
                $order->update([
                    'status' => 'paid',
                    'payment_method' => 'manual',
                ]);

                // Update transaction if exists
                if ($manualPayment->transaction_id) {
                    $transaction = Transaction::find($manualPayment->transaction_id);
                    if ($transaction) {
                        $transaction->update([
                            'status' => 'completed',
                        ]);
                    }
                }

                // Mark product as sold and deliver order
                if ($order->productDetail) {
                    $order->productDetail->update(['is_sold' => true]);
                } elseif ($order->credential) {
                    $order->credential->update([
                        'is_sold' => true,
                        'sold_to_order_id' => $order->id,
                    ]);
                }

                $order->update(['status' => 'delivered']);
            } else {
                // If it's a wallet deposit, credit the wallet
                $wallet = $manualPayment->user->wallet ?? Wallet::create([
                    'user_id' => $manualPayment->user_id,
                    'balance' => 0,
                ]);

                $wallet->increment('balance', $manualPayment->amount);

                // Create transaction
                Transaction::create([
                    'user_id' => $manualPayment->user_id,
                    'wallet_id' => $wallet->id,
                    'type' => 'deposit',
                    'amount' => $manualPayment->amount,
                    'gateway' => 'manual',
                    'status' => 'completed',
                    'reference' => $manualPayment->reference,
                    'description' => 'Manual payment deposit - Approved',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment approved successfully!',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function reject(Request $request, ManualPayment $manualPayment)
    {
        if ($manualPayment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This payment has already been processed.',
            ], 400);
        }

        $manualPayment->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'admin_notes' => $request->admin_notes ?? 'Payment rejected by admin',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment rejected successfully!',
        ]);
    }

    // Banking Details Management
    public function bankingIndex()
    {
        $bankingDetails = BankingDetail::latest()->paginate(20);
        return view('admin.banking-details.index', compact('bankingDetails'));
    }

    public function bankingCreate()
    {
        return view('admin.banking-details.create');
    }

    public function bankingStore(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'account_type' => 'required|in:savings,current',
            'swift_code' => 'nullable|string|max:50',
            'routing_number' => 'nullable|string|max:50',
            'instructions' => 'nullable|string|max:1000',
        ]);

        BankingDetail::create($request->all());

        return redirect()->route('admin.banking-details.index')
            ->with('success', 'Banking detail added successfully!');
    }

    public function bankingEdit(BankingDetail $bankingDetail)
    {
        return view('admin.banking-details.edit', compact('bankingDetail'));
    }

    public function bankingUpdate(Request $request, BankingDetail $bankingDetail)
    {
        $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'account_type' => 'required|in:savings,current',
            'swift_code' => 'nullable|string|max:50',
            'routing_number' => 'nullable|string|max:50',
            'instructions' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $bankingDetail->update($request->all());

        return redirect()->route('admin.banking-details.index')
            ->with('success', 'Banking detail updated successfully!');
    }

    public function bankingDestroy(BankingDetail $bankingDetail)
    {
        // Check if any pending payments use this banking detail
        if ($bankingDetail->manualPayments()->where('status', 'pending')->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete banking detail with pending payments.');
        }

        $bankingDetail->delete();

        return redirect()->route('admin.banking-details.index')
            ->with('success', 'Banking detail deleted successfully!');
    }
}

