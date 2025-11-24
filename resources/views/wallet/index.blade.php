@extends('layouts.app')

@section('title', 'Wallet - BiggestLogs')

@section('content')
<div class="max-w-4xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 py-6 md:py-8 pb-20 md:pb-8">
    <div class="flex items-center justify-between mb-4 md:mb-6">
        <h1 class="text-2xl md:text-3xl font-bold gradient-text">My Wallet 💰</h1>
        <a href="{{ route('dashboard') }}" class="text-yellow-accent hover:text-red-accent transition flex items-center gap-2">
            <span>← Back</span>
        </a>
    </div>

    <!-- Wallet Balance Card -->
    <div class="bg-gradient-to-r from-red-accent via-yellow-accent to-red-accent rounded-xl shadow-2xl shadow-red-accent/30 p-6 md:p-8 text-white mb-6 md:mb-8">
        <p class="text-sm md:text-base mb-2 opacity-90">Available Balance</p>
        <p class="text-4xl md:text-5xl font-bold mb-4">₦{{ number_format($wallet->balance ?? 0, 2) }}</p>
        <div class="grid grid-cols-2 gap-4 text-xs md:text-sm">
            <div>
                <p class="opacity-80">Total Deposited</p>
                <p class="text-lg md:text-xl font-semibold">₦{{ number_format($wallet->total_deposited ?? 0, 2) }}</p>
            </div>
            <div>
                <p class="opacity-80">Total Withdrawn</p>
                <p class="text-lg md:text-xl font-semibold">₦{{ number_format($wallet->total_withdrawn ?? 0, 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Deposit Section -->
    <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-lg p-4 md:p-6 mb-6 md:mb-8">
        <h2 class="text-lg md:text-xl font-bold mb-4 text-gray-200">Fund Wallet</h2>
        <form id="deposit-form">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2 text-gray-300">Amount</label>
                <input type="number" name="amount" step="0.01" min="1" required
                       class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2 text-gray-300">Payment Gateway</label>
                <select name="gateway" required
                        class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none">
                    <option value="paystack" class="bg-dark-300">Paystack</option>
                    <option value="stripe" class="bg-dark-300">Stripe</option>
                    <option value="razorpay" class="bg-dark-300">Razorpay</option>
                    <option value="payvibe" class="bg-dark-300">PayVibe</option>
                    <option value="btcpay" class="bg-dark-300">BTCPay Server</option>
                    <option value="coingate" class="bg-dark-300">CoinGate</option>
                    <option value="manual" class="bg-dark-300">Manual Payment (Bank Transfer)</option>
                </select>
            </div>
            <button type="submit" 
                    class="w-full bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white py-3 rounded-xl font-semibold transition glow-button relative shadow-lg shadow-red-accent/40">
                <span class="relative z-10">Fund Wallet</span>
            </button>
        </form>
    </div>

    <!-- Transaction History -->
    <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-lg p-4 md:p-6">
        <h2 class="text-lg md:text-xl font-bold mb-4 text-gray-200">Transaction History</h2>
        @if($transactions->count() > 0)
        <div class="space-y-4">
            @foreach($transactions as $transaction)
            <div class="border-b border-dark-300 pb-4 last:border-0">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <p class="font-semibold text-gray-200">{{ $transaction->description ?? 'N/A' }}</p>
                        <p class="text-xs md:text-sm text-gray-400">
                            @if(is_object($transaction->created_at))
                                {{ $transaction->created_at->format('M d, Y h:i A') }}
                            @else
                                {{ \Carbon\Carbon::parse($transaction->created_at)->format('M d, Y h:i A') }}
                            @endif
                        </p>
                        @if($transaction->gateway)
                        <p class="text-xs text-gray-500">Via {{ ucfirst($transaction->gateway) }}</p>
                        @endif
                    </div>
                    <div class="text-left sm:text-right">
                        <p class="font-bold text-lg {{ $transaction->type === 'deposit' || $transaction->type === 'refund' ? 'text-green-400' : 'text-red-accent' }}">
                            {{ $transaction->type === 'deposit' || $transaction->type === 'refund' ? '+' : '-' }}₦{{ number_format($transaction->amount ?? 0, 2) }}
                        </p>
                        <span class="text-xs px-2 py-1 rounded 
                            {{ ($transaction->status === 'completed' || $transaction->status === 'approved') ? 'bg-green-600/20 text-green-400 border border-green-500/30' : ($transaction->status === 'failed' || $transaction->status === 'rejected' ? 'bg-red-600/20 text-red-400 border border-red-500/30' : 'bg-yellow-accent/20 text-yellow-accent border border-yellow-accent/30') }}">
                            {{ ucfirst($transaction->status ?? 'pending') }}
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @if($transactions->hasPages())
        <div class="mt-4">
            {{ $transactions->links() }}
        </div>
        @endif
        @else
        <p class="text-gray-400 text-center py-8">No transactions yet.</p>
        @endif
    </div>
</div>

@section('scripts')
<script>
document.getElementById('deposit-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const btn = this.querySelector('button[type="submit"]');
    const btnText = btn.querySelector('span');
    btn.disabled = true;
    btnText.textContent = 'Processing...';
    
    try {
        const response = await fetch('{{ route("wallet.deposit") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // showAlert(data.message, 'success');
            // Redirect to payment gateway
            
            // Handle manual payment redirect
            if (data.redirect) {
                window.location.href = data.redirect;
                return;
            }
            if (data.redirect_url) {
                window.location.href = data.redirect_url;
            }
        } else {
            showAlert(data.message, 'error');
            btn.disabled = false;
            btnText.textContent = 'Fund Wallet';
        }
    } catch (error) {
        showAlert('An error occurred. Please try again.', 'error');
        btn.disabled = false;
        btnText.textContent = 'Fund Wallet';
    }
});
</script>
@endsection
