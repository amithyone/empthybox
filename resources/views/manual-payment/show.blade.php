@extends('layouts.app')

@section('title', 'Payment Details - BiggestLogs')

@section('content')
<div class="max-w-4xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 pt-20 md:pt-24 pb-20 md:pb-8">
    <div class="flex items-center justify-between mb-4 md:mb-6">
        <h1 class="text-2xl md:text-3xl font-bold gradient-text">Payment Details 💳</h1>
        <a href="{{ route('manual-payment.index') }}" class="text-yellow-accent hover:text-red-accent transition flex items-center gap-2">
            <span>← Back to Payments</span>
        </a>
    </div>

    <!-- Payment Status Card -->
    <div class="bg-dark-200 rounded-xl shadow-lg p-6 md:p-8 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-200">Payment Information</h2>
            <span class="px-4 py-2 rounded-lg font-semibold
                @if($manualPayment->status === 'approved') bg-green-500/20 text-green-400 border border-green-500/30
                @elseif($manualPayment->status === 'rejected') bg-red-500/20 text-red-400 border border-red-500/30
                @else bg-yellow-500/20 text-yellow-400 border border-yellow-500/30
                @endif">
                {{ strtoupper($manualPayment->status) }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <p class="text-gray-400 text-sm mb-1">Reference Number</p>
                <p class="text-gray-200 font-semibold">{{ $manualPayment->reference }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-sm mb-1">Amount</p>
                <p class="text-2xl font-bold text-yellow-accent">₦{{ number_format($manualPayment->amount, 2) }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-sm mb-1">Submitted</p>
                <p class="text-gray-200">{{ $manualPayment->created_at->format('M d, Y h:i A') }}</p>
            </div>
            @if($manualPayment->approved_at)
            <div>
                <p class="text-gray-400 text-sm mb-1">{{ $manualPayment->status === 'approved' ? 'Approved' : 'Rejected' }}</p>
                <p class="text-gray-200">{{ $manualPayment->approved_at->format('M d, Y h:i A') }}</p>
            </div>
            @endif
        </div>

        @if($manualPayment->bankingDetail)
        <div class="bg-dark-300 rounded-lg p-4 mb-4">
            <p class="text-gray-400 text-sm mb-2">Bank Account Used</p>
            <p class="text-gray-200 font-semibold">{{ $manualPayment->bankingDetail->bank_name }}</p>
            <p class="text-gray-300">{{ $manualPayment->bankingDetail->account_name }} - {{ $manualPayment->bankingDetail->account_number }}</p>
        </div>
        @endif

        @if($manualPayment->notes)
        <div class="bg-dark-300 rounded-lg p-4 mb-4">
            <p class="text-gray-400 text-sm mb-2">Your Notes</p>
            <p class="text-gray-200">{{ $manualPayment->notes }}</p>
        </div>
        @endif

        @if($manualPayment->admin_notes)
        <div class="bg-blue-500/20 border border-blue-500/50 rounded-lg p-4">
            <p class="text-blue-300 text-sm mb-2 font-semibold">Admin Notes</p>
            <p class="text-blue-200">{{ $manualPayment->admin_notes }}</p>
        </div>
        @endif

        @if($manualPayment->receipt_path)
        <div class="mt-6">
            <p class="text-gray-400 text-sm mb-2">Payment Receipt</p>
            <a href="{{ asset('storage/' . $manualPayment->receipt_path) }}" target="_blank" class="inline-flex items-center gap-2 text-yellow-accent hover:text-red-accent">
                <span>📄 View Receipt</span>
            </a>
        </div>
        @endif
    </div>

    @if($manualPayment->order)
    <div class="bg-dark-200 rounded-xl shadow-lg p-6 md:p-8 mb-6">
        <h2 class="text-xl font-semibold mb-4 text-gray-200">Related Order</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-gray-400 text-sm mb-1">Order Number</p>
                <p class="text-gray-200 font-semibold">{{ $manualPayment->order->order_number }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-sm mb-1">Product</p>
                <p class="text-gray-200">{{ $manualPayment->order->product->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-sm mb-1">Order Status</p>
                <p class="text-gray-200 capitalize">{{ $manualPayment->order->status }}</p>
            </div>
            <div>
                <a href="{{ route('orders.show', $manualPayment->order) }}" class="text-yellow-accent hover:text-red-accent">
                    View Order Details →
                </a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

