@extends('layouts.app')

@section('title', 'My Orders - BiggestLogs')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 py-6 md:py-8 pb-20 md:pb-8">
    <h1 class="text-2xl md:text-3xl font-bold mb-4 md:mb-6 gradient-text">My Orders</h1>

    @if($orders->count() > 0)
    <div class="space-y-4">
        @foreach($orders as $order)
        <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-lg hover:shadow-xl hover:border-yellow-accent/50 transition p-4 md:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex-1">
                    <h3 class="text-lg md:text-xl font-bold mb-2 text-gray-200">{{ $order->product->name }}</h3>
                    <p class="text-xs md:text-sm text-gray-400 mb-2">Order #{{ $order->order_number }}</p>
                    <p class="text-xs md:text-sm mb-2">Amount: <span class="font-semibold text-yellow-accent">${{ number_format($order->amount, 2) }}</span></p>
                    <p class="text-xs md:text-sm mb-2">
                        Status: 
                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                            {{ $order->status === 'completed' ? 'bg-green-600/20 text-green-400 border border-green-500/30' : '' }}
                            {{ $order->status === 'pending' || $order->status === 'paid' ? 'bg-yellow-accent/20 text-yellow-accent border border-yellow-accent/30' : '' }}
                            {{ $order->status === 'delivered' ? 'bg-blue-500/20 text-blue-400 border border-blue-500/30' : '' }}
                            {{ $order->status === 'replaced' ? 'bg-red-accent/20 text-red-accent border border-red-accent/30' : '' }}">
                            {{ ucfirst($order->status ?? 'pending') }}
                        </span>
                    </p>
                    @if($order->has_replacement_request)
                    <p class="text-xs md:text-sm text-yellow-accent mt-2">🔁 Replacement requested</p>
                    @endif
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('orders.show', $order) }}" 
                       class="bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white px-4 py-2 rounded-lg font-medium transition shadow-lg shadow-red-accent/30 text-sm md:text-base">
                        View Details
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($orders->hasPages())
    <div class="mt-6">
        {{ $orders->links() }}
    </div>
    @endif
    @else
    <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-lg p-8 md:p-12 text-center">
        <p class="text-gray-400 text-lg mb-4">You haven't made any orders yet.</p>
        <a href="{{ route('products.index') }}" class="inline-block bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white px-6 py-3 rounded-xl font-semibold transition glow-button relative shadow-lg shadow-red-accent/40">
            <span class="relative z-10">Start Shopping</span>
        </a>
    </div>
    @endif
</div>
@endsection
