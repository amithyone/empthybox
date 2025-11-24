@extends('layouts.app')

@section('title', 'Products - BiggestLogs')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 pt-16 md:pt-20 mt-[100px] pb-20 md:pb-8">
    <div class="flex items-center justify-between mb-4 md:mb-6">
        <h1 class="text-2xl md:text-3xl font-bold gradient-text">Browse Products</h1>
        <a href="{{ route('dashboard') }}" class="text-yellow-accent hover:text-red-accent transition flex items-center gap-2">
            <span>← Back</span>
        </a>
    </div>

    <!-- Category Filter -->
    @if($categories->count() > 0)
    <div class="mb-6 flex flex-wrap gap-2 md:gap-3">
        <a href="{{ route('products.index') }}" 
           class="px-3 md:px-4 py-2 rounded-lg transition {{ !$categorySlug ? 'bg-gradient-to-r from-red-accent to-yellow-accent text-white shadow-lg shadow-red-accent/30' : 'bg-dark-200 border border-dark-300 text-gray-300 hover:border-yellow-accent/50' }}">
            All
        </a>
        @foreach($categories as $category)
        <a href="{{ route('products.index', ['category' => $category->slug]) }}" 
           class="px-3 md:px-4 py-2 rounded-lg transition {{ $categorySlug === $category->slug ? 'bg-gradient-to-r from-red-accent to-yellow-accent text-white shadow-lg shadow-red-accent/30' : 'bg-dark-200 border border-dark-300 text-gray-300 hover:border-yellow-accent/50' }}">
            {{ $category->name }}
        </a>
        @endforeach
    </div>
    @endif

    <!-- Products Grid -->
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
                        View
                    </a>
                </div>
                @if($product->available_stock > 0)
                <div class="text-xs md:text-sm text-green-400 flex items-center gap-1">
                    <span>✓</span> <span>{{ $product->available_stock }} available</span>
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
            <p class="text-gray-500">No products found in this category.</p>
        </div>
        @endforelse
    </div>

    @if($products->hasPages())
    <div class="mt-6 md:mt-8">
        {{ $products->links() }}
    </div>
    @endif
</div>
@endsection


