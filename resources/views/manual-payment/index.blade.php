@extends('layouts.app')

@section('title', 'My Manual Payments - BiggestLogs')

@section('content')
<div class="max-w-4xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 pt-16 md:pt-20 pb-20 md:pb-8">
    <div class="flex items-center justify-between mb-4 md:mb-6">
        <h1 class="text-2xl md:text-3xl font-bold gradient-text">My Manual Payments 💳</h1>
        <a href="{{ route('dashboard') }}" class="text-yellow-accent hover:text-red-accent transition flex items-center gap-2">
            <span>← Back to Dashboard</span>
        </a>
    </div>

    @if($manualPayments->count() > 0)
        <div class="space-y-4">
            @foreach($manualPayments as $payment)
            <div class="bg-dark-200 rounded-xl shadow-lg p-4 md:p-6 hover:shadow-xl transition">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="px-3 py-1 rounded-lg text-xs font-semibold
                                @if($payment->status === 'approved') bg-green-500/20 text-green-400 border border-green-500/30
                                @elseif($payment->status === 'rejected') bg-red-500/20 text-red-400 border border-red-500/30
                                @else bg-yellow-500/20 text-yellow-400 border border-yellow-500/30
                                @endif">
                                {{ strtoupper($payment->status) }}
                            </span>
                            <span class="text-gray-400 text-sm">{{ $payment->reference }}</span>
                        </div>
                        <p class="text-2xl font-bold text-yellow-accent mb-1">₦{{ number_format($payment->amount, 2) }}</p>
                        <p class="text-gray-400 text-sm">{{ $payment->created_at->format('M d, Y h:i A') }}</p>
                        @if($payment->bankingDetail)
                        <p class="text-gray-300 text-sm mt-1">{{ $payment->bankingDetail->bank_name }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('manual-payment.show', $payment) }}" class="px-4 py-2 bg-dark-300 hover:bg-dark-400 text-gray-200 rounded-lg transition">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @if($manualPayments->hasPages())
        <div class="mt-6">
            {{ $manualPayments->links() }}
        </div>
        @endif
    @else
        <div class="bg-dark-200 rounded-xl shadow-lg p-8 text-center">
            <p class="text-gray-400 text-lg mb-4">No manual payments yet.</p>
            <a href="{{ route('dashboard') }}" class="text-yellow-accent hover:text-red-accent">
                Go to Dashboard →
            </a>
        </div>
    @endif
</div>
@endsection

