<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Deposit;
use App\Models\PaymentGateway;
use App\Services\PayVibeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        $wallet = $user->wallet ?? Wallet::create([
            'user_id' => $user->id,
            'balance' => 0,
        ]);
        
        // Get deposits from deposits table
        $deposits = $user->deposits()
            ->latest()
            ->get()
            ->map(function ($deposit) {
                return (object) [
                    'id' => $deposit->id,
                    'type' => 'deposit',
                    'amount' => $deposit->amount,
                    'description' => $deposit->description,
                    'gateway' => $deposit->gateway,
                    'status' => $deposit->status,
                    'reference' => $deposit->reference,
                    'created_at' => $deposit->created_at,
                    'updated_at' => $deposit->updated_at,
                ];
            });
        
        // Get purchases and other transactions (excluding deposits)
        $transactions = $user->transactions()
            ->where('type', '!=', 'deposit')
            ->latest()
            ->get()
            ->map(function ($transaction) {
                return (object) [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'amount' => $transaction->amount,
                    'description' => $transaction->description,
                    'gateway' => $transaction->gateway,
                    'status' => $transaction->status,
                    'reference' => $transaction->reference,
                    'created_at' => $transaction->created_at,
                    'updated_at' => $transaction->updated_at,
                ];
            });

        // Merge and sort by created_at descending
        $allTransactions = $deposits->merge($transactions)
            ->sortByDesc('created_at')
            ->values();

        // Paginate manually
        $perPage = 20;
        $currentPage = request()->get('page', 1);
        $items = $allTransactions->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $allTransactions->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Get active payment gateways
        $paymentGateways = PaymentGateway::getActive();

        return view('wallet.index', compact('wallet', 'transactions', 'paymentGateways'))->with('transactions', $paginated);
    }

    public function deposit(Request $request)
    {
        // Get valid gateway codes from database
        $validGateways = PaymentGateway::where('is_active', true)
            ->where('is_enabled', true)
            ->pluck('code')
            ->toArray();
        
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'gateway' => 'required|in:' . implode(',', $validGateways),
        ]);

        $wallet = auth()->user()->wallet ?? Wallet::create([
            'user_id' => auth()->id(),
            'balance' => 0,
        ]);

        // Handle PayVibe differently
        

        // Handle manual payment
        if ($request->gateway === 'manual') {
            return response()->json([
                'success' => true,
                'message' => 'Redirecting to manual payment form...',
                'redirect' => route('manual-payment.form', ['amount' => $request->amount]),
            ]);
        }

        if ($request->gateway === 'payvibe') {
            return $this->handlePayVibeDeposit($request, $wallet);
        }

        // Create pending deposit for other gateways
        $reference = 'DEP-' . time() . rand(1000, 9999);
        $deposit = Deposit::create([
            'user_id' => auth()->id(),
            'wallet_id' => $wallet->id,
            'amount' => $request->amount,
            'gateway' => $request->gateway,
            'status' => 'pending',
            'reference' => $reference,
            'description' => 'Wallet deposit',
        ]);

        // In production, redirect to payment gateway
        // For now, simulate success
        // In real app, handle webhooks

        return response()->json([
            'success' => true,
            'message' => 'Redirecting to payment gateway...',
            'deposit_id' => $deposit->id,
            // 'redirect_url' => $gateway->getPaymentUrl($deposit),
        ]);
    }

    protected function handlePayVibeDeposit($request, $wallet)
    {
        try {
            // Generate virtual account
            $reference = 'PAYVIBE_' . time() . '_' . auth()->id() . '_' . rand(1000, 9999);
            
            $payVibeService = new PayVibeService();
            $result = $payVibeService->generateVirtualAccount($request->amount, $reference);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to generate payment details',
                    'details' => $result['debug'] ?? null,
                ], 400);
            }

            // Create pending deposit (store NGN as entered by user)
            $deposit = Deposit::create([
                'user_id' => auth()->id(),
                'wallet_id' => $wallet->id,
                'amount' => $request->amount,
                'gateway' => 'payvibe',
                'status' => 'pending',
                'reference' => $reference,
                'description' => 'PayVibe wallet deposit',
                'gateway_response' => $result,
            ]);

            // Create transaction for backward compatibility (will be linked to deposit)
            $transaction = Transaction::create([
                'user_id' => auth()->id(),
                'wallet_id' => $wallet->id,
                'type' => 'deposit',
                'amount' => $request->amount,
                'gateway' => 'payvibe',
                'status' => 'pending',
                'reference' => $reference,
                'description' => 'PayVibe wallet deposit',
                'gateway_response' => $result,
            ]);
            
            // Link deposit to transaction
            $deposit->update(['transaction_id' => $transaction->id]);

            return response()->json([
                'success' => true,
                'redirect_url' => route('payvibe.payment', [
                    'transaction' => $transaction->id,
                    'virtual_account' => $result['virtual_account'],
                    'bank_name' => $result['bank_name'],
                    'account_name' => $result['account_name'],
                    // Pass the original amount user intends to fund
                    'amount' => $result['original_amount'],
                    'charges' => $result['charges'],
                    'reference' => $reference,
                ]),
            ]);

        } catch (\Exception $e) {
            Log::error('PayVibe deposit error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again.',
            ], 500);
        }
    }

    public function showPayVibePayment(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        $virtualAccount = $request->virtual_account;
        $bankName = $request->bank_name;
        $accountName = $request->account_name;
        $amount = $request->amount;
        $charges = $request->charges;
        $reference = $request->reference;

        return view('wallet.payvibe', compact(
            'transaction',
            'virtualAccount',
            'bankName',
            'accountName',
            'amount',
            'charges',
            'reference'
        ));
    }
}


