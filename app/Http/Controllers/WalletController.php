<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Deposit;
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
        
        // Get deposits and purchases separately
        $deposits = $user->deposits()
            ->latest()
            ->paginate(10, ['*'], 'deposits_page');
        
        $purchases = $user->transactions()
            ->where('type', 'purchase')
            ->latest()
            ->paginate(10, ['*'], 'purchases_page');

        return view('wallet.index', compact('wallet', 'deposits', 'purchases'));
    }

    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'gateway' => 'required|in:paystack,stripe,razorpay,payvibe,btcpay,coingate,manual',
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


