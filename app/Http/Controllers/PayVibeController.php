<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\PayVibeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayVibeController extends Controller
{
    protected $payVibeService;

    public function __construct(PayVibeService $payVibeService)
    {
        $this->middleware('auth');
        $this->payVibeService = $payVibeService;
    }

    /**
     * Generate virtual account for wallet funding
     */
    public function generateAccount(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100|max:10000000'
        ]);

        try {
            $user = Auth::user();
            $amount = $request->amount;

            // Generate unique reference
            $reference = 'PAYVIBE_' . time() . '_' . $user->id . '_' . rand(1000, 9999);

            // Create pending transaction
            $wallet = $user->wallet ?? Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
            ]);

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'type' => 'deposit',
                'amount' => $amount,
                'gateway' => 'payvibe',
                'status' => 'pending',
                'reference' => $reference,
                'description' => 'PayVibe wallet deposit',
            ]);

            // Generate virtual account from PayVibe
            $result = $this->payVibeService->generateVirtualAccount($amount, $reference);

            if (!$result['success']) {
                // Transaction was created but PayVibe failed
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to generate virtual account. Please try again.',
                ], 400);
            }

            // Update transaction with additional details
            $transaction->update([
                'gateway_response' => $result
            ]);

            Log::info('PayVibe: Virtual account generated', [
                'transaction_id' => $transaction->id,
                'reference' => $reference,
                'amount' => $amount
            ]);

            return response()->json([
                'success' => true,
                'transaction_id' => $transaction->id,
                'virtual_account' => $result['virtual_account'],
                'bank_name' => $result['bank_name'],
                'account_name' => $result['account_name'],
                'amount_to_pay' => $result['amount'],
                'amount_credited' => $result['original_amount'],
                'charges' => $result['charges'],
                'reference' => $reference,
            ]);

        } catch (\Exception $e) {
            Log::error('PayVibe: Exception in generateAccount', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again.',
            ], 500);
        }
    }

    /**
     * Verify/check payment status manually
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'reference' => 'required|string'
        ]);

        try {
            $transaction = Transaction::where('reference', $request->reference)
                ->where('user_id', Auth::id())
                ->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found.',
                ], 404);
            }

            // If our DB already marked it completed, short-circuit
            if ($transaction->status === 'completed') {
                return response()->json([
                    'success' => true,
                    'status' => 'paid',
                    'message' => 'Payment already confirmed. Wallet has been credited.',
                ]);
            }

            // Check with PayVibe API
            $verification = $this->payVibeService->verifyPayment($request->reference);

            if (!$verification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to verify payment status.',
                ], 400);
            }

            // Parse status and amounts
            $status = $verification['status'] ?? $verification['transaction_status'] ?? null;
            $transactionAmount = $verification['transaction_amount'] ?? $verification['amount'] ?? null; // total paid in NGN

            if ($status === 'success' || $status === 'completed' || $status === 'successful') {
                // Mirror webhook logic: x = (transactionAmount - 100) / 1.015
                if ($transaction->status === 'pending') {
                    DB::transaction(function () use ($transaction, $transactionAmount) {
                        $fixedCharge = 100;
                        $rate = 1.015;
                        $originalAmount = $transactionAmount ? round((($transactionAmount - $fixedCharge) / $rate), 2) : $transaction->amount;

                        $transaction->update([
                            'status' => 'completed',
                            'amount' => $originalAmount,
                            'final_amount' => $transactionAmount,
                        ]);

                        $wallet = $transaction->wallet;
                        $wallet->increment('balance', $originalAmount);
                        $wallet->increment('total_deposited', $originalAmount);
                    });
                }

                return response()->json([
                    'success' => true,
                    'status' => 'paid',
                    'message' => 'Payment verified successfully! Wallet has been credited.',
                ]);
            }

            return response()->json([
                'success' => false,
                'status' => 'pending',
                'message' => 'Payment still pending. Please check again in a moment.',
            ]);

        } catch (\Exception $e) {
            Log::error('PayVibe: Exception in checkStatus', [
                'error' => $e->getMessage(),
                'reference' => $request->reference
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again.',
            ], 500);
        }
    }

    /**
     * Process successful payment and credit wallet
     */
    protected function processSuccessfulPayment($transaction)
    {
        DB::transaction(function () use ($transaction) {
            // Update transaction status
            $transaction->update(['status' => 'completed']);

            // Credit wallet
            $wallet = $transaction->wallet;
            $wallet->increment('balance', $transaction->amount);
            $wallet->increment('total_deposited', $transaction->amount);

            Log::info('PayVibe: Wallet credited', [
                'transaction_id' => $transaction->id,
                'user_id' => $transaction->user_id,
                'amount' => $transaction->amount,
                'new_balance' => $wallet->balance
            ]);
        });
    }
}

