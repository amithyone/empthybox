@extends('layouts.app')

@section('title', $product->name . ' - BiggestLogs')

@section('content')
<div class="max-w-4xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 pt-20 md:pt-24 pb-20 md:pb-8">
    <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-2xl shadow-red-accent/10 p-4 md:p-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-200">{{ $product->name }}</h1>
            <a href="{{ route('products.index') }}" class="text-yellow-accent hover:text-red-accent transition flex items-center gap-2">
                <span>← Back to Products</span>
            </a>
        </div>
        
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <span class="text-2xl md:text-3xl font-bold bg-gradient-to-r from-red-accent to-yellow-accent bg-clip-text text-transparent">₦{{ number_format($product->price, 2) }}</span>
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
                <div><strong class="text-gray-300">Region:</strong> {{ $product->flag ?? '' }} {{ $product->region }}</div>
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

        <!-- Account Guidelines Button -->
        <div class="mb-6 text-center">
            <button onclick="document.getElementById('guidelines-modal').classList.remove('hidden'); document.getElementById('guidelines-modal').classList.add('flex');" 
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-6 py-3 rounded-xl font-semibold transition glow-button relative shadow-lg shadow-blue-600/40">
                <span class="relative z-10">📋 Account Guidelines</span>
            </button>
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
                            <option value="wallet" class="bg-dark-300">Wallet (Balance: ₦{{ number_format(auth()->user()->wallet->balance ?? 0, 2) }})</option>
                            @endif
                            <option value="paystack" class="bg-dark-300">Paystack</option>
                            <option value="stripe" class="bg-dark-300">Stripe</option>
                            <option value="razorpay" class="bg-dark-300">Razorpay</option>
                            <option value="payvibe" class="bg-dark-300">PayVibe</option>
                            <option value="btcpay" class="bg-dark-300">BTCPay Server</option>
                            <option value="coingate" class="bg-dark-300">CoinGate</option>
                            <option value="manual" class="bg-dark-300">Manual Payment (Bank Transfer)</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white py-3 rounded-xl font-semibold transition glow-button relative shadow-lg shadow-red-accent/40 hover:shadow-xl hover:shadow-yellow-accent/50">
                        <span class="relative z-10">Purchase for ₦{{ number_format($product->price, 2) }}</span>
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

<!-- Account Guidelines Modal -->
<div id="guidelines-modal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm z-[9999] items-center justify-center px-4 py-8 overflow-y-auto">
    <div class="bg-dark-200 border-2 border-yellow-accent rounded-2xl shadow-2xl max-w-4xl w-full p-6 md:p-8 my-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-200">📋 Account Guidelines</h2>
            <button onclick="document.getElementById('guidelines-modal').classList.add('hidden');" 
                    class="text-gray-400 hover:text-gray-200 text-2xl font-bold transition">&times;</button>
        </div>
        
        <div class="prose prose-invert max-w-none">
            <p class="text-gray-300 mb-4">
                Below are the account guides that help you to work with all the accounts provided by our store. Please, note we only register the accounts, so we can't fully advise you. You can find all the basic information on any questions you might have.
            </p>

            <!-- Use High-Level Proxies -->
            <div class="bg-dark-300 border border-blue-500/30 rounded-xl p-4 md:p-6 mb-6">
                <h3 class="text-xl font-bold text-blue-400 mb-3">✅ Use High-Level Proxy Servers</h3>
                <p class="text-gray-300 font-semibold mb-2">The Problem:</p>
                <p class="text-gray-400 mb-3">If you log in to multiple accounts using only one IP address, all your accounts may be blocked.</p>
                <p class="text-gray-300 font-semibold mb-2">The Solution:</p>
                <p class="text-gray-400 mb-4">Use high-level proxy servers. High-level proxies or high-quality ones are individual IPv4 proxy servers (make sure you have exclusive access).</p>
                
                <p class="text-red-400 font-semibold mb-2">❌ What Shouldn't Be Done:</p>
                <ul class="list-disc list-inside text-gray-400 space-y-2 mb-4">
                    <li>Do not use proxy servers packages like fineproxy, proxymir, etc;</li>
                    <li>Do not use applications like Hola, FreeVPN, etc. to change the IP. VPN services provide access to one IP address for several people at once and may be considered as shared proxies.</li>
                    <li>IPv6 proxies are not recommended for use.</li>
                </ul>
                
                <p class="text-yellow-accent font-semibold mb-2">⚠️ Important:</p>
                <p class="text-gray-400 mb-3">It is impossible to use IPv6 for Vkontakte social network.</p>
                <p class="text-gray-300 font-semibold mb-2">✓ Conclusion:</p>
                <p class="text-gray-400">Use high-level proxy servers. If you authorize two or more accounts, you must use different proxy servers according to our store rules.</p>
            </div>

            <!-- Use Different Devices -->
            <div class="bg-dark-300 border border-green-500/30 rounded-xl p-4 md:p-6 mb-6">
                <h3 class="text-xl font-bold text-green-400 mb-3">✅ Use Different Devices When Logging In</h3>
                <p class="text-gray-300 font-semibold mb-2">The Problem:</p>
                <p class="text-gray-400 mb-3">When you log in to multiple accounts from one device (computer, phone, tablet, etc.), all your accounts may be blocked.</p>
                <p class="text-gray-300 font-semibold mb-2">The Solution:</p>
                <p class="text-gray-400 mb-4">Use various special programs and services.</p>
                
                <p class="text-red-400 font-semibold mb-2">❌ What Are NOT Different Devices:</p>
                <ul class="list-disc list-inside text-gray-400 space-y-2 mb-4">
                    <li>Usual browser mode and incognito one;</li>
                    <li>Cleaning cookies of browsers;</li>
                    <li>Different browsers.</li>
                </ul>
                
                <p class="text-green-400 font-semibold mb-2">✓ What Are the Different Devices:</p>
                <ul class="list-disc list-inside text-gray-400 space-y-2 mb-4">
                    <li>Computer, one more computer;</li>
                    <li>Phone, one more phone;</li>
                    <li>Special program for logging in to the accounts;</li>
                    <li>Changing UserAgent in the browser and other subsequent actions in the browser;</li>
                    <li>Use of antidetect tools that change the device data by themselves. For example, <strong class="text-green-400">GeeLark</strong>. It lets you control multiple Android phones or create multiple browser profiles for managing accounts.</li>
                </ul>
                
                <p class="text-gray-300 font-semibold mb-2">✓ Conclusion:</p>
                <p class="text-gray-400">Use different devices or special programs. If you authorize two or more accounts, you must use different proxy servers according to our store rules.</p>
                <p class="text-gray-300 text-sm mt-3">
                    <em>📋 The list of special programs and services can be found on the "Software and services" page.</em>
                </p>
            </div>

            <!-- Limits and Humanlike Actions -->
            <div class="bg-dark-300 border border-yellow-500/30 rounded-xl p-4 md:p-6 mb-6">
                <h3 class="text-xl font-bold text-yellow-accent mb-3">✅ Limits and Humanlike Actions</h3>
                <p class="text-gray-300 font-semibold mb-2">The Problem:</p>
                <p class="text-gray-400 mb-3">If you immediately start doing mass operations on an account (making thousands of likes, sending hundreds of messages, etc.), it will be blocked quickly.</p>
                <p class="text-gray-300 font-semibold mb-2">The Solution:</p>
                <p class="text-gray-400">To be secured, first you must do some common actions that a normal person would do after registering. Example: fill out the page, subscribe to several people, like some posts, add some photos, make a few reposts, comments, etc.</p>
            </div>

            <!-- Important Notice -->
            <div class="bg-yellow-accent/10 border-2 border-yellow-accent rounded-xl p-4 md:p-6">
                <p class="text-yellow-accent font-bold text-lg mb-2">⚠️ Important Notice</p>
                <p class="text-gray-300">
                    We are not responsible for the program/service developers and proxies providers. All the accounts are registered by us or by our partners using the private software (programs that are not available for public access) and proxies servers, which we also did by ourselves (they are not available for the public too).
                </p>
            </div>
        </div>
        
        <div class="mt-6 text-center">
            <button onclick="document.getElementById('guidelines-modal').classList.add('hidden');" 
                    class="bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white px-8 py-3 rounded-xl font-semibold transition glow-button relative shadow-lg shadow-red-accent/40">
                <span class="relative z-10">Got It! ✓</span>
            </button>
        </div>
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
            btnText.textContent = 'Purchase for ₦{{ number_format($product->price, 2) }}';
        }
    } catch (error) {
        showAlert('An error occurred. Please try again.', 'error');
        btn.disabled = false;
        btnText.textContent = 'Purchase for ₦{{ number_format($product->price, 2) }}';
    }
});
</script>
@endsection
