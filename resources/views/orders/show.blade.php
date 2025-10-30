@extends('layouts.app')

@section('title', 'Order Details - BiggestLogs')

@section('content')
<div class="max-w-4xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 py-6 md:py-8 pb-20 md:pb-8">
    <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-2xl shadow-red-accent/10 p-4 md:p-6">
        <h1 class="text-2xl md:text-3xl font-bold mb-4 md:mb-6 gradient-text">Order Details</h1>

        <!-- Order Info -->
        <div class="bg-dark-300 border border-dark-400 p-4 md:p-6 rounded-xl mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-xs md:text-sm text-gray-400">Order Number</p>
                    <p class="font-semibold text-gray-200">{{ $order->order_number }}</p>
                </div>
                <div>
                    <p class="text-xs md:text-sm text-gray-400">Status</p>
                    <p class="font-semibold">
                        <span class="px-3 py-1 rounded-full text-xs md:text-sm
                            {{ $order->status === 'completed' ? 'bg-green-600/20 text-green-400 border border-green-500/30' : '' }}
                            {{ $order->status === 'pending' || $order->status === 'paid' ? 'bg-yellow-accent/20 text-yellow-accent border border-yellow-accent/30' : '' }}
                            {{ $order->status === 'delivered' ? 'bg-blue-500/20 text-blue-400 border border-blue-500/30' : '' }}">
                            {{ ucfirst($order->status ?? 'pending') }}
                        </span>
                    </p>
                </div>
                <div>
                    <p class="text-xs md:text-sm text-gray-400">Product</p>
                    <p class="font-semibold text-gray-200">{{ $order->product->name }}</p>
                </div>
                <div>
                    <p class="text-xs md:text-sm text-gray-400">Amount</p>
                    <p class="font-semibold bg-gradient-to-r from-red-accent to-yellow-accent bg-clip-text text-transparent text-lg md:text-xl">${{ number_format($order->amount, 2) }}</p>
                </div>
            </div>
            <p class="text-xs md:text-sm text-gray-400">Ordered on {{ $order->created_at->format('M d, Y h:i A') }}</p>
        </div>

        <!-- Credentials Reveal Section -->
        @if($order->status === 'delivered' || $order->status === 'completed')
        <div class="border-2 border-yellow-accent/40 rounded-xl p-4 md:p-6 mb-6 bg-dark-300/30" id="credentials-section">
            @if(false)
            <div class="bg-dark-300 border border-dark-400 p-4 rounded-xl">
                <p class="font-semibold mb-3 text-gray-200">Credentials</p>
                <div id="credentials-display" class="space-y-2 text-gray-300">
                    @if($order->credential)
                    <p><strong class="text-gray-400">Username:</strong> <span class="font-mono text-yellow-accent">{{ $order->credential->username }}</span></p>
                    <p><strong class="text-gray-400">Password:</strong> <span class="font-mono text-yellow-accent">{{ $order->credential->password }}</span></p>
                    @if($order->credential->email)
                    <p><strong class="text-gray-400">Email:</strong> <span class="font-mono text-yellow-accent">{{ $order->credential->email }}</span></p>
                    @endif
                    @endif
                </div>
                <p class="text-xs md:text-sm text-gray-400 mt-4">PIN already used on {{ $order->pin ? $order->pin->used_at->format('M d, Y h:i A') : 'N/A' }}</p>
            </div>
            @else
            <div class="text-center">
                <h3 class="text-lg md:text-xl font-bold mb-3 text-gray-200">🔒 Reveal Your Credentials</h3>
                <p class="text-gray-400 mb-4 text-sm md:text-base">Enter your 4-digit PIN to reveal your account credentials</p>
                <form id="pin-form" class="max-w-xs mx-auto">
                    @csrf
                    <input type="text" id="pin-input" name="pin" 
                           maxlength="6" pattern="[0-9]{4,6}" 
                           class="text-center text-2xl md:text-3xl font-bold tracking-widest w-full bg-dark-300 border-2 border-yellow-accent/50 text-gray-200 rounded-xl p-4 mb-4 focus:ring-2 focus:ring-yellow-accent focus:border-yellow-accent transition outline-none"
                           placeholder="0000">
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white py-3 rounded-xl font-semibold transition glow-button relative shadow-lg shadow-red-accent/40">
                        <span class="relative z-10">Reveal Credentials</span>
                    </button>
                </form>
                <div id="credentials-display" class="hidden mt-6 p-4 bg-dark-300 border border-dark-400 rounded-xl text-left break-words overflow-x-auto">
                    <!-- Credentials will appear here -->
                </div>
            </div>
            @endif
        </div>
        @endif

        <!-- Replacement Request -->
        @if(($order->status === 'completed' || $order->status === 'delivered') && !$order->has_replacement_request && !$order->is_replaced)
        <div class="border-2 border-yellow-accent/40 rounded-xl p-4 md:p-6 mb-6 bg-dark-300/30">
            <h3 class="text-lg md:text-xl font-bold mb-2 text-gray-200">🔁 Request Replacement</h3>
            <p class="text-gray-400 mb-4 text-sm md:text-base">If your log doesn't work, we'll replace it fast — no stress, no delay.</p>
            <form id="replacement-form">
                @csrf
                <textarea name="message" rows="3" 
                          class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 mb-4 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none resize-none" 
                          placeholder="Describe the issue..."></textarea>
                <button type="submit" 
                        class="bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white px-6 py-2 rounded-xl font-semibold transition glow-button relative shadow-lg shadow-red-accent/40">
                    <span class="relative z-10">Request Replacement</span>
                </button>
            </form>
        </div>
        @endif

        @if($order->has_replacement_request)
        <div class="bg-dark-300 border-l-4 border-yellow-accent p-4 mb-6 rounded-r-lg">
            <p class="font-semibold text-yellow-accent">🔁 Replacement Requested</p>
            <p class="text-sm text-gray-400">We've got your back, your replacement is on the way!</p>
        </div>
        @endif

        @if($order->is_replaced)
        <div class="bg-dark-300 border-l-4 border-green-500 p-4 mb-6 rounded-r-lg">
            <p class="font-semibold text-green-400">✓ Replacement Completed</p>
            <p class="text-sm text-gray-400">Your replacement log is ready! Enter your PIN above to reveal.</p>
        </div>
        @endif

        <!-- Back Button -->
        <div class="mt-6">
            <a href="{{ route('orders.index') }}" class="text-yellow-accent font-semibold hover:text-red-accent transition">← Back to Orders</a>
        </div>
    </div>
</div>

@section('scripts')
<script>
// PIN Reveal
document.getElementById('pin-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const pin = document.getElementById('pin-input').value;
    const btn = this.querySelector('button[type="submit"]');
    
    if (pin.length !== 4) {
        showAlert('Please enter a 4-digit PIN', 'error');
        return;
    }
    
    btn.disabled = true;
    btn.textContent = 'Verifying...';
    
    try {
        const formData = new FormData();
        formData.append('pin', pin);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        
        const response = await fetch('{{ route("orders.reveal", $order) }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            const display = document.getElementById('credentials-display');
            display.classList.remove('hidden');
            display.innerHTML = `
                <p class="font-semibold mb-3 text-gray-200">Your Credentials:</p>
                <p class="mb-2 text-gray-300"><strong class="text-gray-400">Username:</strong> <span class="font-mono text-yellow-accent">${data.credentials.username}</span></p>
                <p class="mb-2 text-gray-300"><strong class="text-gray-400">Password:</strong> <span class="font-mono text-yellow-accent">${data.credentials.password}</span></p>
                ${data.credentials.email ? `<p class="mb-2 text-gray-300"><strong class="text-gray-400">Email:</strong> <span class="font-mono text-yellow-accent">${data.credentials.email}</span></p>` : ''}
                        ${data.credentials.authenticator_code ? `<p class=\"mb-2 text-gray-300\"><strong class=\"text-gray-400\">Authenticator Code:</strong> <span class=\"font-mono text-yellow-accent\">${data.credentials.authenticator_code}</span></p>` : ''}
                        ${data.credentials.authenticator_site ? `<p class=\"mb-2 text-gray-300\"><strong class=\"text-gray-400\">Authenticator Site:</strong> <span class=\"font-mono text-yellow-accent\">${data.credentials.authenticator_site}</span></p>` : ''}
                <div class="mt-4 p-3 bg-yellow-accent/20 border border-yellow-accent/30 rounded-xl text-xs md:text-sm text-yellow-accent">
                    Tips: Save credentials securely. You can return to this order to view again.
                </div>
            `;
                    // Keep form visible to allow repeat reveals if needed
            showAlert(data.message, 'success');
        } else {
            showAlert(data.message, 'error');
            btn.disabled = false;
            btn.textContent = 'Reveal Credentials';
        }
    } catch (error) {
        showAlert('An error occurred. Please try again.', 'error');
        btn.disabled = false;
        btn.textContent = 'Reveal Credentials';
    }
});

// Replacement Request
document.getElementById('replacement-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Submitting...';
    
    try {
        const response = await fetch('{{ route("orders.replacement", $order) }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => location.reload(), 2000);
        } else {
            showAlert(data.message, 'error');
            btn.disabled = false;
            btn.textContent = 'Request Replacement';
        }
    } catch (error) {
        showAlert('An error occurred. Please try again.', 'error');
        btn.disabled = false;
        btn.textContent = 'Request Replacement';
    }
});
</script>
@endsection


