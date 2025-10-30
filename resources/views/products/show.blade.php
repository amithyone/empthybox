@extends('layouts.app')

@section('title', $product->name . ' - BiggestLogs')

@section('content')
<div class="max-w-4xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 py-6 md:py-8 pb-20 md:pb-8">
    <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-2xl shadow-red-accent/10 p-4 md:p-6">
        <h1 class="text-2xl md:text-3xl font-bold mb-4 text-gray-200">{{ $product->name }}</h1>
        
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <span class="text-2xl md:text-3xl font-bold bg-gradient-to-r from-red-accent to-yellow-accent bg-clip-text text-transparent">${{ number_format($product->price, 2) }}</span>
            @if($product->available_stock > 0)
            <span class="bg-green-600/20 text-green-400 border border-green-500/30 px-4 py-2 rounded-lg text-sm md:text-base">✓ {{ $product->available_stock }} in stock</span>
            @else
            <span class="bg-red-accent/20 text-red-400 border border-red-accent/30 px-4 py-2 rounded-lg text-sm md:text-base">✗ Out of stock</span>
            @endif
        </div>

        <!-- Preview Info -->
        @if($product->preview_info || $product->account_type || $product->region)
        <div class="bg-dark-300 border border-dark-400 p-4 rounded-xl mb-6">
            <h3 class="font-semibold mb-3 text-gray-300">Account Preview</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-gray-400">
                @if($product->account_type)
                <div><strong class="text-gray-300">Type:</strong> {{ $product->account_type }}</div>
                @endif
                @if($product->region)
                <div><strong class="text-gray-300">Region:</strong> {{ $product->region }}</div>
                @endif
                @if($product->is_verified)
                <div><strong class="text-gray-300">Status:</strong> <span class="text-yellow-accent">✓ Verified</span></div>
                @endif
            </div>
        </div>
        @endif

        <!-- Description -->
        <div class="mb-6">
            <h3 class="text-lg md:text-xl font-semibold mb-3 text-gray-200">Description</h3>
            <p class="text-gray-400 whitespace-pre-line">{{ $product->description }}</p>
        </div>

        <!-- Login Steps -->
        @if($product->login_steps)
        <div class="mb-6">
            <h3 class="text-lg md:text-xl font-semibold mb-3 text-gray-200">Login Steps</h3>
            <div class="bg-dark-300 border border-dark-400 p-4 rounded-xl">
                <p class="text-gray-400 whitespace-pre-line">{{ $product->login_steps }}</p>
            </div>
        </div>
        @endif

        <!-- Access Tips -->
        @if($product->access_tips)
        <div class="mb-6">
            <h3 class="text-lg md:text-xl font-semibold mb-3 text-gray-200">Access Tips</h3>
            <div class="bg-dark-300 border border-yellow-accent/30 p-4 rounded-xl">
                <p class="text-gray-400 whitespace-pre-line">{{ $product->access_tips }}</p>
            </div>
        </div>
        @endif

        <!-- Additional Instructions -->
        @if($product->additional_instructions)
        <div class="mb-6">
            <h3 class="text-lg md:text-xl font-semibold mb-3 text-gray-200">Additional Instructions</h3>
            <div class="bg-dark-300 border border-green-500/30 p-4 rounded-xl">
                <p class="text-gray-400 whitespace-pre-line">{{ $product->additional_instructions }}</p>
            </div>
        </div>
        @endif

        <!-- Replacement Policy -->
        <div class="bg-dark-300 border-l-4 border-yellow-accent p-4 mb-6 rounded-r-lg">
            <p class="font-semibold text-yellow-accent mb-1">🔁 Replacement Policy</p>
            <p class="text-gray-400 text-sm">We replace bad logs fast — no stress, no delay. If your log doesn't work, simply request a replacement from your order page.</p>
        </div>

        <!-- Purchase Button -->
        @auth
            @if($product->available_stock > 0)
            <div class="border-t border-dark-300 pt-6">
                <h3 class="text-lg md:text-xl font-semibold mb-4 text-gray-200">Purchase Options</h3>
                <form id="purchase-form" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium mb-2 text-gray-300">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none" required>
                            @if(auth()->user()->wallet && (auth()->user()->wallet->balance ?? 0) >= $product->price)
                            <option value="wallet" class="bg-dark-300">Wallet (Balance: ${{ number_format(auth()->user()->wallet->balance ?? 0, 2) }})</option>
                            @endif
                            <option value="paystack" class="bg-dark-300">Paystack</option>
                            <option value="stripe" class="bg-dark-300">Stripe</option>
                            <option value="razorpay" class="bg-dark-300">Razorpay</option>
                            <option value="payvibe" class="bg-dark-300">PayVibe</option>
                            <option value="btcpay" class="bg-dark-300">BTCPay Server</option>
                            <option value="coingate" class="bg-dark-300">CoinGate</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white py-3 rounded-xl font-semibold transition glow-button relative shadow-lg shadow-red-accent/40 hover:shadow-xl hover:shadow-yellow-accent/50">
                        <span class="relative z-10">Purchase for ${{ number_format($product->price, 2) }}</span>
                    </button>
                </form>
            </div>
            @else
            <div class="bg-dark-300 p-4 rounded-xl text-center">
                <p class="text-gray-400">This product is currently out of stock.</p>
            </div>
            @endif
        @else
        <div class="border-t border-dark-300 pt-6 text-center">
            <a href="{{ route('login') }}" class="inline-block bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white px-8 py-3 rounded-xl font-semibold transition glow-button relative shadow-lg shadow-red-accent/40">
                <span class="relative z-10">Login to Purchase</span>
            </a>
        </div>
        @endauth
    </div>
</div>

@section('scripts')
<script>
document.getElementById('purchase-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const btn = this.querySelector('button[type="submit"]');
    const btnText = btn.querySelector('span');
    btn.disabled = true;
    btnText.textContent = 'Processing...';

    try {
        const response = await fetch('{{ route("orders.store", $product) }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();
        
        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1500);
        } else {
            showAlert(data.message, 'error');
            btn.disabled = false;
            btnText.textContent = 'Purchase for ${{ number_format($product->price, 2) }}';
        }
    } catch (error) {
        showAlert('An error occurred. Please try again.', 'error');
        btn.disabled = false;
        btnText.textContent = 'Purchase for ${{ number_format($product->price, 2) }}';
    }
});
</script>
@endsection
