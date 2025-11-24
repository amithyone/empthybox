@extends('layouts.app')

@section('title', 'Admin - Products')

@section('content')
<div class="max-w-6xl mx-auto px-4 pt-16 md:pt-20 mt-[100px] pb-6" style="margin-top: 100px;">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold gradient-text">Products</h1>
    <a href="{{ route('admin.dashboard') }}" class="text-yellow-accent hover:text-red-accent transition">← Back to Dashboard</a>
  </div>

  @if(session('success'))
  <div class="bg-green-600/20 border border-green-500/30 text-green-300 rounded p-3 mb-4">{{ session('success') }}</div>
  @endif

  <div class="bg-dark-200 border-2 border-dark-300 rounded-xl p-4 mb-6">
    <h2 class="text-lg font-semibold mb-3">Add Product</h2>
    <form method="post" action="{{ route('admin.products.store') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3">
      @csrf
      <input name="name" placeholder="Name" class="bg-dark-300 border-2 border-dark-400 rounded p-2 md:col-span-2" required>
      <input name="slug" placeholder="Slug (optional)" class="bg-dark-300 border-2 border-dark-400 rounded p-2">
      <select name="category_id" class="bg-dark-300 border-2 border-dark-400 rounded p-2" required>
        <option value="">Select category</option>
        @foreach($categories as $cat)
        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
        @endforeach
      </select>
      <input name="price" type="number" min="0" step="0.01" placeholder="Price (NGN)" class="bg-dark-300 border-2 border-dark-400 rounded p-2" required>
      <label class="flex items-center gap-2 text-sm text-gray-300"><input type="checkbox" name="is_active" value="1" checked> Active</label>
      <div class="md:col-span-6">
        <button class="w-full bg-gradient-to-r from-red-accent to-yellow-accent text-white rounded p-3 font-semibold">Create</button>
      </div>
    </form>
  </div>

  <div class="bg-dark-200 border-2 border-dark-300 rounded-xl p-4">
    <h2 class="text-lg font-semibold mb-3">All Products</h2>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="text-gray-400">
          <tr>
            <th class="text-left p-2">Name</th>
            <th class="text-left p-2">Category</th>
            <th class="text-left p-2">Price (₦)</th>
            <th class="text-left p-2">Status</th>
            <th class="text-left p-2">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($products as $product)
          <tr class="border-t border-dark-300">
            <td class="p-2">{{ $product->name }}</td>
            <td class="p-2">{{ $product->category->name ?? '-' }}</td>
            <td class="p-2">₦{{ number_format($product->price, 2) }}</td>
            <td class="p-2">{{ $product->is_active ? 'Active' : 'Disabled' }}</td>
            <td class="p-2">
              <form method="post" action="{{ route('admin.products.update', $product) }}" class="grid grid-cols-1 md:grid-cols-5 gap-2 items-center">
                @csrf
                <input name="name" value="{{ $product->name }}" class="bg-dark-300 border-2 border-dark-400 rounded p-2" required>
                <input name="slug" value="{{ $product->slug }}" class="bg-dark-300 border-2 border-dark-400 rounded p-2">
                <select name="category_id" class="bg-dark-300 border-2 border-dark-400 rounded p-2">
                  @foreach($categories as $cat)
                  <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                  @endforeach
                </select>
                <input name="price" type="number" min="0" step="0.01" value="{{ $product->price }}" class="bg-dark-300 border-2 border-dark-400 rounded p-2">
                <label class="flex items-center gap-2 text-sm text-gray-300"><input type="checkbox" name="is_active" value="1" {{ $product->is_active ? 'checked' : '' }}> Active</label>
                <div class="md:col-span-5">
                  <button class="bg-yellow-accent text-black rounded px-3 py-2 font-semibold">Save</button>
                </div>
              </form>
              <div class="mt-3">
                <a href="{{ route('admin.products.demo.download') }}" class="text-blue-400 hover:text-blue-300 text-xs underline mb-2 inline-block">📥 Download Demo Format</a>
                <form method="post" action="{{ route('admin.products.details.upload', $product) }}" enctype="multipart/form-data">
                  @csrf
                  <div class="flex flex-col md:flex-row gap-2 items-center">
                    <input type="file" name="accounts_file" accept=".txt" class="bg-dark-300 border-2 border-dark-400 rounded p-2 w-full md:w-auto" required>
                    <button class="bg-green-500 text-black rounded px-3 py-2 font-semibold">Upload TXT (add stock)</button>
                    <span class="text-xs text-gray-400">In stock: {{ $product->available_stock }}</span>
                  </div>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr><td class="p-2 text-gray-400" colspan="5">No products yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection


