@extends('layouts.app')

@section('title', 'Select SMS Provider - BiggestLogs')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 pt-20 md:pt-24 pb-20 md:pb-8">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold gradient-text">📱 Choose SMS Provider</h1>
            <p class="text-xs md:text-sm text-gray-400 mt-1">Pick a provider to request numbers and receive codes. You can switch anytime.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="text-yellow-accent hover:text-red-accent transition flex items-center gap-2">
            <span>← Back</span>
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-6">
        <a href="{{ route('sms.index', ['provider' => 'smspool']) }}" class="block bg-dark-200 border-2 border-dark-300 rounded-2xl p-6 md:p-8 hover:border-yellow-accent transition shadow-lg shadow-red-accent/10">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 md:gap-4">
                    <div class="w-12 h-12 md:w-14 md:h-14 rounded-xl bg-gradient-to-br from-red-accent to-yellow-accent flex items-center justify-center text-white font-bold text-lg md:text-xl">S</div>
                    <div>
                        <div class="text-lg md:text-xl font-semibold text-gray-200">SMSPool</div>
                        <div class="text-xs md:text-sm text-gray-400 mt-1">Broad coverage • Fast</div>
                    </div>
                </div>
                <span class="text-[10px] md:text-xs px-2 md:px-3 py-1 md:py-1.5 rounded-full {{ $activeProvider === 'smspool' ? 'bg-yellow-accent/20 text-yellow-accent border border-yellow-accent/30' : 'bg-dark-300 text-gray-400 border border-dark-400' }}">{{ $activeProvider === 'smspool' ? 'Active' : 'Select' }}</span>
            </div>
            <div class="mt-4 md:mt-5">
                <span class="inline-block text-[11px] md:text-xs bg-dark-300 border border-dark-400 text-gray-400 px-2 md:px-3 py-1 md:py-1.5 rounded">Recommended</span>
            </div>
        </a>

        <a href="{{ route('sms.index', ['provider' => 'tigersms']) }}" class="block bg-dark-200 border-2 border-dark-300 rounded-2xl p-6 md:p-8 hover:border-yellow-accent transition shadow-lg shadow-red-accent/10">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 md:gap-4">
                    <div class="w-12 h-12 md:w-14 md:h-14 rounded-xl bg-gradient-to-br from-yellow-accent to-red-accent flex items-center justify-center text-white font-bold text-lg md:text-xl">T</div>
                    <div>
                        <div class="text-lg md:text-xl font-semibold text-gray-200">TigerSMS</div>
                        <div class="text-xs md:text-sm text-gray-400 mt-1">Alternative • Flexible</div>
                    </div>
                </div>
                <span class="text-[10px] md:text-xs px-2 md:px-3 py-1 md:py-1.5 rounded-full {{ $activeProvider === 'tigersms' ? 'bg-yellow-accent/20 text-yellow-accent border border-yellow-accent/30' : 'bg-dark-300 text-gray-400 border border-dark-400' }}">{{ $activeProvider === 'tigersms' ? 'Active' : 'Select' }}</span>
            </div>
        </a>
    </div>

    <div class="mt-6 md:mt-8">
        <a href="{{ route('sms.inbox') }}" class="inline-flex items-center gap-2 text-yellow-accent hover:text-red-accent transition text-sm">
            <span>Go to Inbox</span>
            <span>→</span>
        </a>
    </div>
</div>
@endsection


