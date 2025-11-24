@extends('layouts.app')

@section('title', 'SMS Service Coming Soon - BiggestLogs')

@section('content')
<div class="max-w-4xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 pt-20 md:pt-24 pb-20 md:pb-8 text-center">
    <div class="bg-gradient-to-br from-yellow-accent/10 via-red-accent/10 to-yellow-accent/10 border-2 border-yellow-accent/30 rounded-xl md:rounded-2xl p-8 md:p-12 mb-6 relative overflow-hidden">
        <div class="absolute top-0 right-0 text-8xl md:text-9xl opacity-10">🚀</div>
        <div class="relative z-10">
            <div class="text-6xl md:text-7xl mb-4">📱</div>
            <h1 class="text-3xl md:text-4xl font-bold mb-4 gradient-text">SMS Verification Service</h1>
            <div class="flex items-center justify-center gap-2 bg-yellow-accent/20 border border-yellow-accent/50 rounded-xl p-4 mb-6 inline-flex">
                <span class="text-2xl">🚀</span>
                <span class="text-lg md:text-xl font-bold text-yellow-accent">Coming Soon</span>
            </div>
            <p class="text-base md:text-lg text-gray-300 mb-6 max-w-2xl mx-auto">
                Our Premium SMS Verification Service is under development. We're working hard to bring you instant SMS verification codes from multiple providers with fast delivery, worldwide coverage, and secure, reliable service.
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-8 mb-8">
                <div class="bg-dark-200/50 border border-dark-300 p-4 rounded-lg text-center backdrop-blur-sm">
                    <div class="text-3xl mb-2">⚡</div>
                    <div class="font-semibold text-gray-200 text-sm md:text-base">Instant Delivery</div>
                    <div class="text-xs text-gray-400 mt-1">Get codes in seconds</div>
                </div>
                <div class="bg-dark-200/50 border border-dark-300 p-4 rounded-lg text-center backdrop-blur-sm">
                    <div class="text-3xl mb-2">🌍</div>
                    <div class="font-semibold text-gray-200 text-sm md:text-base">Worldwide Coverage</div>
                    <div class="text-xs text-gray-400 mt-1">Multiple countries</div>
                </div>
                <div class="bg-dark-200/50 border border-dark-300 p-4 rounded-lg text-center backdrop-blur-sm">
                    <div class="text-3xl mb-2">🔒</div>
                    <div class="font-semibold text-gray-200 text-sm md:text-base">Secure & Reliable</div>
                    <div class="text-xs text-gray-400 mt-1">Protected delivery</div>
                </div>
            </div>
            <div class="mt-6">
                <a href="{{ route('home') }}" class="inline-block bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white px-6 py-3 rounded-xl font-semibold transition glow-button relative shadow-lg shadow-red-accent/40">
                    <span class="relative z-10">← Back to Home</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection


