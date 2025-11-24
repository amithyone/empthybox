@extends('layouts.app')

@section('title', 'BiggestLogs - Premium Digital Marketplace')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 pt-16 md:pt-20 mt-[100px] pb-20 md:pb-8" style="margin-top: 100px;">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-red-accent/20 via-yellow-accent/10 to-red-dark/20 border border-red-accent/30 rounded-xl md:rounded-2xl p-6 md:p-8 mb-6 md:mb-8 text-center backdrop-blur-sm shadow-xl shadow-red-accent/10">
        <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-3 md:mb-4 gradient-text">🔥 BiggestLogs</h1>
        <p class="text-lg md:text-xl mb-2 text-gray-300">Premium Digital Marketplace</p>
        <p class="text-base md:text-lg text-gray-400">Fast Replacements, Verified Logs, Secure Delivery</p>
        <div class="mt-4 md:mt-6 text-yellow-accent font-semibold text-sm md:text-base">
            🔁 We replace bad logs fast — no stress, no delay.
        </div>
    </div>

    <!-- SMS Service Feature -->
    <div class="bg-gradient-to-br from-yellow-accent/10 via-red-accent/10 to-yellow-accent/10 border-2 border-yellow-accent/30 rounded-xl md:rounded-2xl p-6 md:p-8 mb-6 md:mb-8 relative overflow-hidden">
        <div class="absolute top-0 right-0 text-8xl md:text-9xl opacity-10">📱</div>
        <div class="relative z-10">
            <div class="flex items-center justify-center gap-3 mb-4">
                <span class="text-3xl md:text-4xl">📱</span>
                <h2 class="text-2xl md:text-3xl font-bold gradient-text">Premium SMS Service</h2>
            </div>
            @if(\App\Models\Setting::get('sms_coming_soon', false) && (!auth()->check() || (auth()->check() && !auth()->user()->is_admin)))
            <p class="text-base md:text-lg text-gray-300 mb-4 max-w-2xl mx-auto">
                Receive SMS verification codes instantly from multiple providers. Fast delivery, reliable service, and worldwide coverage.
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6 mb-6">
                <div class="bg-dark-200/50 border border-dark-300 p-4 rounded-lg text-center backdrop-blur-sm">
                    <div class="text-2xl mb-2">⚡</div>
                    <div class="font-semibold text-gray-200 text-sm md:text-base">Instant Delivery</div>
                    <div class="text-xs text-gray-400 mt-1">Get codes in seconds</div>
                </div>
                <div class="bg-dark-200/50 border border-dark-300 p-4 rounded-lg text-center backdrop-blur-sm">
                    <div class="text-2xl mb-2">🌍</div>
                    <div class="font-semibold text-gray-200 text-sm md:text-base">Worldwide Coverage</div>
                    <div class="text-xs text-gray-400 mt-1">Multiple countries</div>
                </div>
                <div class="bg-dark-200/50 border border-dark-300 p-4 rounded-lg text-center backdrop-blur-sm">
                    <div class="text-2xl mb-2">🔒</div>
                    <div class="font-semibold text-gray-200 text-sm md:text-base">Secure & Reliable</div>
                    <div class="text-xs text-gray-400 mt-1">Protected delivery</div>
                </div>
            </div>
            <div class="flex items-center justify-center gap-2 bg-yellow-accent/20 border border-yellow-accent/50 rounded-xl p-4 mb-6">
                <span class="text-2xl">🚀</span>
                <span class="text-lg md:text-xl font-bold text-yellow-accent">Coming Soon</span>
            </div>
            @else
            <p class="text-base md:text-lg text-gray-300 mb-4 max-w-2xl mx-auto">
                Receive SMS verification codes instantly from multiple providers. Fast delivery, reliable service, and worldwide coverage.
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6 mb-6">
                <div class="bg-dark-200/50 border border-dark-300 p-4 rounded-lg text-center backdrop-blur-sm">
                    <div class="text-2xl mb-2">⚡</div>
                    <div class="font-semibold text-gray-200 text-sm md:text-base">Instant Delivery</div>
                    <div class="text-xs text-gray-400 mt-1">Get codes in seconds</div>
                </div>
                <div class="bg-dark-200/50 border border-dark-300 p-4 rounded-lg text-center backdrop-blur-sm">
                    <div class="text-2xl mb-2">🌍</div>
                    <div class="font-semibold text-gray-200 text-sm md:text-base">Worldwide Coverage</div>
                    <div class="text-xs text-gray-400 mt-1">Multiple countries</div>
                </div>
                <div class="bg-dark-200/50 border border-dark-300 p-4 rounded-lg text-center backdrop-blur-sm">
                    <div class="text-2xl mb-2">🔒</div>
                    <div class="font-semibold text-gray-200 text-sm md:text-base">Secure & Reliable</div>
                    <div class="text-xs text-gray-400 mt-1">Protected delivery</div>
                </div>
            </div>
            @auth
                <a href="{{ route('sms.select') }}" class="inline-block bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white px-6 py-3 rounded-xl font-semibold transition glow-button relative shadow-lg shadow-red-accent/40">
                    <span class="relative z-10">Get SMS Code Now →</span>
                </a>
            @else
                <a href="{{ route('register') }}" class="inline-block bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white px-6 py-3 rounded-xl font-semibold transition glow-button relative shadow-lg shadow-red-accent/40">
                    <span class="relative z-10">Sign Up to Get Started →</span>
                </a>
            @endauth
            @endif
        </div>
    </div>

    <!-- Categories -->
    @if($categories->count() > 0)
    <div class="mb-6 md:mb-8">
        <h2 class="text-xl md:text-2xl font-bold mb-3 md:mb-4 text-gray-200">Browse Categories</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 md:gap-4">
            @foreach($categories as $category)
            <a href="{{ route('products.index', ['category' => $category->slug]) }}" 
               class="bg-dark-200 border border-dark-300 hover:border-yellow-accent/50 p-4 md:p-6 rounded-lg hover:shadow-xl hover:shadow-yellow-accent/20 transition-all text-center group relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-red-accent/0 via-yellow-accent/0 to-red-accent/0 group-hover:from-red-accent/10 group-hover:via-yellow-accent/10 group-hover:to-red-accent/10 transition-all"></div>
                <div class="text-3xl md:text-4xl mb-2 group-hover:scale-110 transition-transform relative z-10">{{ $category->icon ?? '📦' }}</div>
                <h3 class="font-semibold text-sm md:text-base text-gray-300 group-hover:text-yellow-accent transition relative z-10">{{ $category->name }}</h3>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Products -->
    <div>
        <h2 class="text-xl md:text-2xl font-bold mb-4 md:mb-6 text-gray-200">Featured Products</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
            @forelse($products as $product)
            <div class="bg-dark-200 border-2 border-dark-300 hover:border-yellow-accent/60 rounded-lg overflow-hidden hover:shadow-2xl hover:shadow-yellow-accent/30 transition-all group relative">
                <div class="absolute inset-0 bg-gradient-to-br from-red-accent/0 via-yellow-accent/0 to-red-accent/0 group-hover:from-red-accent/10 group-hover:via-yellow-accent/10 group-hover:to-red-accent/10 transition-all rounded-lg"></div>
                <div class="p-4 md:p-6 relative z-10">
                    <div class="flex items-start justify-between mb-2">
                        <h3 class="text-lg md:text-xl font-bold text-gray-200 group-hover:text-yellow-accent transition pr-2">{{ $product->name }}</h3>
                        @if($product->is_verified)
                        <span class="flex-shrink-0 text-xs bg-gradient-to-r from-red-accent to-yellow-accent text-white px-2 py-1 rounded shadow-lg shadow-red-accent/40">✓ Verified</span>
                        @endif
                    </div>
                    <p class="text-gray-400 text-xs md:text-sm mb-4 line-clamp-2">{{ \Illuminate\Support\Str::limit($product->description, 100) }}</p>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                        <span class="text-2xl md:text-3xl font-bold bg-gradient-to-r from-red-accent to-yellow-accent bg-clip-text text-transparent drop-shadow-lg">₦{{ number_format($product->price, 2) }}</span>
                        <a href="{{ route('products.show', $product) }}" 
                           class="bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white px-4 py-2 rounded-lg font-medium transition text-center text-sm md:text-base glow-button relative shadow-lg shadow-red-accent/40 hover:shadow-xl hover:shadow-yellow-accent/50">
                            View Details
                        </a>
                    </div>
                    @if($product->available_stock > 0)
                    <div class="text-xs md:text-sm text-green-400 flex items-center gap-1">
                        <span>✓</span> <span>{{ $product->available_stock }} in stock</span>
                    </div>
                    @else
                    <div class="text-xs md:text-sm text-red-400 flex items-center gap-1">
                        <span>✗</span> <span>Out of stock</span>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-12">
                <p class="text-gray-500">No products available at the moment.</p>
            </div>
            @endforelse
        </div>
        
        @if($products->hasPages())
        <div class="mt-6 md:mt-8">
            {{ $products->links() }}
        </div>
        @endif
    </div>
</div>
@endsection


