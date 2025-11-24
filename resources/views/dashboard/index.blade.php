@extends('layouts.app')

@section('title', 'Dashboard - BiggestLogs')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 py-6 md:py-8 pb-20 md:pb-8">
    <h1 class="text-2xl md:text-3xl font-bold mb-4 md:mb-6 gradient-text">Welcome back, {{ $user->name }}! 👋</h1>

    <!-- PIN Setup Alert -->
    @if(empty($user->pin_hash))
    <div class="bg-gradient-to-r from-yellow-accent/20 via-red-accent/10 to-yellow-accent/20 border-2 border-yellow-accent rounded-xl shadow-lg p-4 md:p-6 mb-6 md:mb-8">
        <div class="flex flex-col md:flex-row items-start md:items-center md:justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-3xl">🔒</span>
                    <h3 class="text-lg md:text-xl font-bold text-yellow-accent">Setup Your PIN</h3>
                </div>
                <p class="text-sm md:text-base text-gray-300">You need to set up a 4-6 digit PIN to securely view your order credentials. This is a one-time setup.</p>
            </div>
            <button onclick="document.getElementById('pin-setup-modal').classList.remove('hidden');" 
                    class="bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white px-6 py-3 rounded-xl font-semibold transition glow-button relative shadow-lg shadow-red-accent/30 whitespace-nowrap">
                <span class="relative z-10">Setup PIN →</span>
            </button>
        </div>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mb-6 md:mb-8">
        <div class="bg-dark-200 border-2 border-dark-300 p-4 md:p-6 rounded-xl shadow-lg hover:border-yellow-accent/50 transition">
            <div class="text-xl md:text-2xl font-bold bg-gradient-to-r from-red-accent to-yellow-accent bg-clip-text text-transparent">{{ $stats['total_orders'] }}</div>
            <div class="text-xs md:text-sm text-gray-400 mt-1">Total Orders</div>
        </div>
        <div class="bg-dark-200 border-2 border-dark-300 p-4 md:p-6 rounded-xl shadow-lg hover:border-yellow-accent/50 transition">
            <div class="text-xl md:text-2xl font-bold text-green-400">₦{{ number_format($stats['wallet_balance'], 2) }}</div>
            <div class="text-xs md:text-sm text-gray-400 mt-1">Wallet Balance</div>
        </div>
        <div class="bg-dark-200 border-2 border-dark-300 p-4 md:p-6 rounded-xl shadow-lg hover:border-yellow-accent/50 transition">
            <div class="text-xl md:text-2xl font-bold text-yellow-accent">{{ $stats['pending_orders'] }}</div>
            <div class="text-xs md:text-sm text-gray-400 mt-1">Pending Orders</div>
        </div>
        <div class="bg-dark-200 border-2 border-dark-300 p-4 md:p-6 rounded-xl shadow-lg hover:border-yellow-accent/50 transition">
            <div class="text-xl md:text-2xl font-bold text-red-accent">{{ $stats['open_tickets'] }}</div>
            <div class="text-xs md:text-sm text-gray-400 mt-1">Open Tickets</div>
        </div>
    </div>

    <!-- SMS Service Card -->
    <div class="bg-gradient-to-r from-yellow-accent/20 via-red-accent/10 to-yellow-accent/20 border-2 border-yellow-accent/30 rounded-xl shadow-lg p-4 md:p-6 mb-6 md:mb-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center sm:justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-3xl md:text-4xl">📱</span>
                    <h3 class="text-lg md:text-xl font-bold gradient-text">SMS Verification Service</h3>
                </div>
                <p class="text-sm md:text-base text-gray-300 mb-3">Receive SMS verification codes instantly. Fast delivery, worldwide coverage.</p>
                @if(\App\Models\Setting::get('sms_coming_soon', false) && !auth()->user()->is_admin)
                <div class="flex items-center gap-2 bg-yellow-accent/20 border border-yellow-accent/50 rounded-xl p-2 inline-flex">
                    <span class="text-lg">🚀</span>
                    <span class="text-sm font-bold text-yellow-accent">Coming Soon</span>
                </div>
                @endif
            </div>
            @if(\App\Models\Setting::get('sms_coming_soon', false) && !auth()->user()->is_admin)
            <span class="bg-gray-700/50 text-gray-500 cursor-not-allowed px-6 py-3 rounded-xl font-semibold whitespace-nowrap">Coming Soon</span>
            @else
            <a href="{{ route('sms.select') }}" class="bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white px-6 py-3 rounded-xl font-semibold transition glow-button relative shadow-lg shadow-red-accent/30 whitespace-nowrap">
                <span class="relative z-10">Get SMS Code →</span>
            </a>
            @endif
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 md:gap-4 mb-6 md:mb-8">
        <a href="{{ route('products.index') }}" class="bg-gradient-to-br from-red-accent to-yellow-accent text-white p-4 md:p-6 rounded-xl shadow-lg text-center hover:shadow-xl hover:shadow-yellow-accent/50 transition transform hover:scale-105">
            <div class="text-2xl md:text-3xl mb-2">🛍️</div>
            <div class="font-semibold text-sm md:text-base">Shop</div>
        </a>
        <a href="{{ route('orders.index') }}" class="bg-dark-200 border-2 border-dark-300 text-gray-300 p-4 md:p-6 rounded-xl shadow-lg text-center hover:border-yellow-accent/50 transition transform hover:scale-105">
            <div class="text-2xl md:text-3xl mb-2">📦</div>
            <div class="font-semibold text-sm md:text-base">My Orders</div>
        </a>
        <a href="{{ route('wallet.index') }}" class="bg-dark-200 border-2 border-dark-300 text-gray-300 p-4 md:p-6 rounded-xl shadow-lg text-center hover:border-yellow-accent/50 transition transform hover:scale-105">
            <div class="text-2xl md:text-3xl mb-2">💰</div>
            <div class="font-semibold text-sm md:text-base">Wallet</div>
        </a>
        @if(!\App\Models\Setting::get('sms_coming_soon', false) || auth()->user()->is_admin)
        <a href="{{ route('sms.inbox') }}" class="bg-dark-200 border-2 border-dark-300 text-gray-300 p-4 md:p-6 rounded-xl shadow-lg text-center hover:border-yellow-accent/50 transition transform hover:scale-105">
            <div class="text-2xl md:text-3xl mb-2">📬</div>
            <div class="font-semibold text-sm md:text-base">Inbox</div>
        </a>
        @else
        <div class="bg-dark-200/50 border-2 border-dark-300/50 text-gray-500 p-4 md:p-6 rounded-xl shadow-lg text-center opacity-50">
            <div class="text-2xl md:text-3xl mb-2">📬</div>
            <div class="font-semibold text-sm md:text-base">Coming Soon</div>
        </div>
        @endif
        <a href="{{ route('tickets.index') }}" class="bg-dark-200 border-2 border-dark-300 text-gray-300 p-4 md:p-6 rounded-xl shadow-lg text-center hover:border-yellow-accent/50 transition transform hover:scale-105">
            <div class="text-2xl md:text-3xl mb-2">🎫</div>
            <div class="font-semibold text-sm md:text-base">Support</div>
        </a>
    </div>

    <!-- Recent Orders -->
    <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-2xl shadow-red-accent/10 p-4 md:p-6 mb-6 md:mb-8">
        <h2 class="text-lg md:text-xl font-bold mb-4 text-gray-200">Recent Orders</h2>
        @if($orders->count() > 0)
        <div class="space-y-4">
            @foreach($orders as $order)
            <div class="border-b border-dark-300 pb-4 last:border-0">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <div class="font-semibold text-gray-200">{{ $order->product->name }}</div>
                        <div class="text-xs md:text-sm text-gray-400">Order #{{ $order->order_number }}</div>
                        <div class="text-xs md:text-sm mt-1">
                            <span class="px-2 py-1 rounded {{ $order->status === 'completed' ? 'bg-green-600/20 text-green-400' : 'bg-yellow-accent/20 text-yellow-accent' }}">
                                {{ ucfirst($order->status ?? 'pending') }}
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('orders.show', $order) }}" class="bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white px-4 py-2 rounded-lg font-medium transition text-sm md:text-base shadow-lg shadow-red-accent/30 self-start sm:self-auto">
                        View
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-4 text-center">
            <a href="{{ route('orders.index') }}" class="text-yellow-accent font-semibold hover:text-red-accent transition">View All Orders →</a>
        </div>
        @else
        <div class="text-center py-8">
            <p class="text-gray-400 mb-4">No orders yet. Start shopping!</p>
            <a href="{{ route('products.index') }}" class="inline-block bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white px-6 py-3 rounded-lg font-medium transition shadow-lg shadow-red-accent/30">
                Browse Products
            </a>
        </div>
        @endif
    </div>
</div>

<!-- PIN Setup Modal -->
@if(empty($user->pin_hash))
<div id="pin-setup-modal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm z-[9999] items-center justify-center px-4">
    <div class="bg-dark-200 border-2 border-yellow-accent rounded-2xl shadow-2xl max-w-md w-full p-6 md:p-8">
        <div class="text-center mb-6">
            <div class="text-5xl mb-3">🔒</div>
            <h2 class="text-2xl font-bold text-gray-200 mb-2">Setup Your PIN</h2>
            <p class="text-gray-400 text-sm">This PIN will be used to view your order credentials securely</p>
        </div>
        
        <form id="pin-setup-form" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-2 text-gray-300">4-6 Digit PIN</label>
                <input type="text" name="pin" required maxlength="6" pattern="[0-9]{4,6}"
                       class="w-full bg-dark-300 border-2 border-yellow-accent/50 rounded-xl p-4 text-gray-200 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none text-center text-2xl tracking-widest"
                       placeholder="0000">
                <p class="text-xs text-gray-500 mt-1">Choose a PIN between 4-6 digits</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2 text-gray-300">Confirm PIN</label>
                <input type="text" name="pin_confirmation" required maxlength="6" pattern="[0-9]{4,6}"
                       class="w-full bg-dark-300 border-2 border-yellow-accent/50 rounded-xl p-4 text-gray-200 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none text-center text-2xl tracking-widest"
                       placeholder="0000">
            </div>
            <button type="submit" 
                    class="w-full bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white py-4 rounded-xl font-semibold transition-all glow-button relative shadow-lg shadow-red-accent/40">
                <span class="relative z-10">Set PIN</span>
            </button>
        </form>
        
        <p class="text-xs text-gray-500 text-center mt-4">
            <span class="text-yellow-accent">⚠️ Remember this PIN!</span> You'll need it every time you view credentials.
        </p>
    </div>
</div>

@section('scripts')
<script>
document.getElementById('pin-setup-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const btn = this.querySelector('button[type="submit"]');
    const btnText = btn.querySelector('span');
    btn.disabled = true;
    btnText.textContent = 'Setting...';

    try {
        const response = await fetch('{{ route("setup-pin") }}', {
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
                location.reload();
            }, 1000);
        } else {
            showAlert(data.message || 'Validation errors occurred', 'error');
            btn.disabled = false;
            btnText.textContent = 'Set PIN';
        }
    } catch (error) {
        showAlert('An error occurred. Please try again.', 'error');
        btn.disabled = false;
        btnText.textContent = 'Set PIN';
    }
});
</script>
@endsection
@endif


