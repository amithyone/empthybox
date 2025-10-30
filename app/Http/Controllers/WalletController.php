<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;

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
        
        $transactions = $user->transactions()
            ->latest()
            ->paginate(20);

        return view('wallet.index', compact('wallet', 'transactions'));
    }

    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'gateway' => 'required|in:paystack,stripe,razorpay,payvibe,btcpay,coingate',
        ]);

        $wallet = auth()->user()->wallet ?? Wallet::create([
            'user_id' => auth()->id(),
            'balance' => 0,
        ]);

        // Create pending transaction
        $transaction = Transaction::create([
            'user_id' => auth()->id(),
            'wallet_id' => $wallet->id,
            'type' => 'deposit',
            'amount' => $request->amount,
            'gateway' => $request->gateway,
            'status' => 'pending',
            'reference' => 'DEP-' . time() . rand(1000, 9999),
            'description' => 'Wallet deposit',
        ]);

        // In production, redirect to payment gateway
        // For now, simulate success
        // In real app, handle webhooks

        return response()->json([
            'success' => true,
            'message' => 'Redirecting to payment gateway...',
            'transaction_id' => $transaction->id,
            // 'redirect_url' => $gateway->getPaymentUrl($transaction),
        ]);
    }
}


