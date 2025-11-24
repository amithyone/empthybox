<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\DepositReceipt;
use Illuminate\Support\Facades\Log;

class PayVibeWebhookController extends Controller
{
    /**
     * Handle PayVibe webhook notifications
     */
    public function handle(Request $request)
    {
        Log::info('PayVibe Webhook: Received', [
            'payload' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        try {
            $payload = $request->all();
            $reference = $payload['reference'] ?? $payload['ref'] ?? null;
            $status = $payload['status'] ?? $payload['transaction_status'] ?? null;
            $amount = $payload['transaction_amount'] ?? $payload['amount'] ?? null;
            $netAmount = $payload['net_amount'] ?? null;
            $creditedAt = $payload['credited_at'] ?? null;

            if (!$reference) {
                Log::error('PayVibe Webhook: Missing reference', ['payload' => $payload]);
                return response()->json(['error' => 'Missing reference'], 400);
            }
            
            // If there's a credited_at timestamp, treat as successful payment
            if ($creditedAt && !$status) {
                $status = 'success';
                Log::info('PayVibe Webhook: Detected success from credited_at', ['reference' => $reference]);
            }

            // Find transaction
            $transaction = Transaction::where('reference', $reference)->first();

            if (!$transaction) {
                Log::error('PayVibe Webhook: Transaction not found', ['reference' => $reference]);
                return response()->json(['error' => 'Transaction not found'], 404);
            }

            // Check if already processed
            if ($transaction->status === 'completed') {
                Log::info('PayVibe Webhook: Already processed', ['reference' => $reference]);
                return response()->json(['message' => 'Transaction already processed']);
            }

            // Process successful payment
            if ($status === 'success' || $status === 'completed' || $status === 'successful') {
                DB::transaction(function () use ($transaction, $amount, $netAmount) {
                    // User paid this full amount (e.g., 1115)
                    $transactionAmount = $amount ?? $transaction->amount;
                    
                    // Calculate original amount before charges: x + 100 + 0.015x = transactionAmount
                    // Solving: 1.015x + 100 = transactionAmount
                    // x = (transactionAmount - 100) / 1.015
                    $fixedCharge = 100;
                    $rate = 1.015; // 1 + 1.5%
                    $originalAmount = round(($transactionAmount - $fixedCharge) / $rate, 2); // Amount in NGN (e.g., 1000)
                    $calculatedCharges = round($transactionAmount - $originalAmount, 2);
                    
                    // Store both amounts for reference
                    $transaction->update([
                        'status' => 'completed',
                        'amount' => $originalAmount, // What user gets credited (e.g., 1000)
                        'final_amount' => $transactionAmount, // What user paid (e.g., 1115)
                    ]);

                    // Credit wallet with the original amount (NGN)
                    $wallet = $transaction->wallet;
                    $oldBalance = $wallet->balance;
                    
                    Log::info('PayVibe Webhook: Before increment', [
                        'transaction_id' => $transaction->id,
                        'user_id' => $transaction->user_id,
                        'old_balance' => $oldBalance,
                        'amount_to_add' => $originalAmount,
                    ]);
                    
                    $wallet->increment('balance', $originalAmount);
                    $wallet->increment('total_deposited', $originalAmount);

                    // Create deposit record
                    \App\Models\Deposit::create([
                        'user_id' => $transaction->user_id,
                        'wallet_id' => $wallet->id,
                        'transaction_id' => $transaction->id,
                        'amount' => $originalAmount,
                        'final_amount' => $transactionAmount,
                        'gateway' => 'payvibe',
                        'reference' => $transaction->reference,
                        'status' => 'completed',
                        'description' => 'PayVibe wallet deposit',
                        'gateway_response' => $transaction->gateway_response,
                        'completed_at' => now(),
                    ]);

                    Log::info('PayVibe Webhook: Wallet credited', [
                        'transaction_id' => $transaction->id,
                        'user_id' => $transaction->user_id,
                        'transaction_amount' => $transactionAmount,
                        'calculated_charges' => $calculatedCharges,
                        'original_amount_credited' => $originalAmount,
                        'old_balance' => $oldBalance,
                        'new_balance' => $wallet->fresh()->balance
                    ]);
                });

                // Send receipt email
                try {
                    Mail::to($transaction->user->email)->send(new DepositReceipt($transaction->fresh()));
                } catch (\Exception $e) {
                    Log::error('Deposit receipt email failed', ['error' => $e->getMessage(), 'transaction_id' => $transaction->id]);
                }

                return response()->json(['message' => 'Payment processed successfully']);
            }

            return response()->json(['message' => 'Webhook received']);

        } catch (\Exception $e) {
            Log::error('PayVibe Webhook: Processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}

