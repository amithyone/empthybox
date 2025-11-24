@extends('layouts.app')

@section('title', 'SMS Inbox - BiggestLogs')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 py-6 md:py-8 pb-20 md:pb-8">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl md:text-3xl font-bold gradient-text">📬 SMS Inbox</h1>
            <a href="{{ route('dashboard') }}" class="text-yellow-accent hover:text-red-accent transition flex items-center gap-2">
                <span>← Back</span>
            </a>
        </div>
        <a href="{{ route('sms.select') }}" class="bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white px-4 py-2 rounded-lg font-medium transition glow-button">
            Request New Number
        </a>
    </div>

    @if($orders->count() > 0)
        <div class="space-y-4">
            @foreach($orders as $order)
                <div class="bg-dark-200 border-2 {{ $order->status === 'completed' ? 'border-green-500/50' : ($order->status === 'active' ? 'border-yellow-accent/50' : 'border-dark-300') }} rounded-xl shadow-lg p-4 md:p-6 hover:shadow-xl transition" data-status="{{ $order->status }}">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-start gap-3 mb-2">
                                <div class="flex-shrink-0 w-10 h-10 rounded-lg {{ $order->status === 'completed' ? 'bg-green-500/20' : ($order->status === 'active' ? 'bg-yellow-accent/20' : 'bg-gray-500/20') }} flex items-center justify-center">
                                    @if($order->status === 'completed')
                                        <span class="text-xl">✅</span>
                                    @elseif($order->status === 'active')
                                        <span class="text-xl">⏳</span>
                                    @else
                                        <span class="text-xl">⏸️</span>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-bold text-lg text-gray-200 mb-1">
                                        {{ $order->service_name ?? 'Service #' . $order->service_id }}
                                    </h3>
                                    <div class="flex flex-wrap gap-2 text-sm text-gray-400">
                                        @if($order->country_name)
                                            <span>📍 {{ $order->country_name }}</span>
                                        @endif
                                        <span>📅 {{ $order->created_at->format('M d, Y H:i') }}</span>
                                        <span class="px-2 py-0.5 rounded {{ $order->status === 'completed' ? 'bg-green-500/20 text-green-400' : ($order->status === 'active' ? 'bg-yellow-accent/20 text-yellow-accent' : 'bg-gray-500/20 text-gray-400') }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            @if($order->phone_number)
                                <div class="bg-dark-300 rounded-lg p-3 mb-3">
                                    <p class="text-xs text-gray-400 mb-1">Phone Number:</p>
                                    <p class="text-lg font-mono font-bold text-gray-200">{{ $order->phone_number }}</p>
                                    <p class="text-xs text-gray-500 mt-2">Copy this number to use for verification</p>
                                </div>
                            @endif

                            @if($order->sms_code)
                                <div class="bg-gradient-to-r from-green-500/20 to-green-600/20 rounded-lg p-4 border border-green-500/30">
                                    <p class="text-xs text-gray-400 mb-2">Verification Code:</p>
                                    <p class="text-3xl font-mono font-bold text-green-400 mb-2 text-center">{{ $order->sms_code }}</p>
                                    @if($order->sms_text)
                                        <p class="text-sm text-gray-300 mt-2 text-center">{{ $order->sms_text }}</p>
                                    @endif
                                    @if($order->sms_received_at)
                                        <p class="text-xs text-gray-400 mt-2 text-center">Received at {{ $order->sms_received_at->format('M d, Y H:i') }}</p>
                                    @endif
                                </div>
                            @elseif($order->status === 'active')
                                <div class="bg-dark-300 rounded-lg p-4 text-center">
                                    <p class="text-gray-300 mb-2">⏳ Waiting for SMS code...</p>
                                    <button onclick="checkStatus({{ $order->provider_order_id ? "'{$order->provider_order_id}'" : $order->id }})" 
                                            class="bg-gradient-to-r from-yellow-accent to-yellow-600 hover:from-yellow-dark hover:to-yellow-700 text-white px-4 py-2 rounded-lg font-medium transition">
                                        Check for Code
                                    </button>
                                </div>
                            @endif

                            @if($order->expires_at && $order->status !== 'completed')
                                <p class="text-xs text-gray-500 mt-2">
                                    ⏰ Expires: {{ $order->expires_at->format('M d, Y H:i') }}
                                    @if($order->expires_at->isPast())
                                        <span class="text-red-accent">(Expired)</span>
                                    @else
                                        <span>({{ $order->expires_at->diffForHumans() }})</span>
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @else
        <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-lg p-8 md:p-12 text-center">
            <div class="text-6xl mb-4">📭</div>
            <h3 class="text-xl font-bold text-gray-200 mb-2">No SMS Orders Yet</h3>
            <p class="text-gray-400 mb-6">Request your first SMS verification number to get started!</p>
            <a href="{{ route('sms.select') }}" class="inline-block bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white px-6 py-3 rounded-lg font-medium transition glow-button">
                Request Number
            </a>
        </div>
    @endif
</div>

<script>
function checkStatus(orderId) {
    const btn = event.target;
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Checking...';

    fetch('{{ route("sms.check-status") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            order_id: orderId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.sms_code) {
            showAlert('SMS code received: ' + data.sms_code, 'success');
            setTimeout(() => location.reload(), 2000);
        } else {
            showAlert(data.error || data.message || 'No code received yet. Please try again in a moment.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred. Please try again.', 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.textContent = originalText;
    });
}

// Auto-refresh active orders every 30 seconds
document.addEventListener('DOMContentLoaded', function() {
    const hasActiveOrders = document.querySelectorAll('[data-status="active"]').length > 0;
    if (hasActiveOrders) {
        setInterval(() => {
            location.reload();
        }, 30000);
    }
});
</script>
@endsection

