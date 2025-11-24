@extends('layouts.app')

@section('title', 'Admin - Categories')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold gradient-text">Categories</h1>
    <a href="{{ route('admin.dashboard') }}" class="text-yellow-accent hover:text-red-accent transition">← Back to Dashboard</a>
  </div>

  @if(session('success'))
  <div class="bg-green-600/20 border border-green-500/30 text-green-300 rounded p-3 mb-4">{{ session('success') }}</div>
  @endif

  <div class="bg-dark-200 border-2 border-dark-300 rounded-xl p-4 mb-6">
    <h2 class="text-lg font-semibold mb-3">Create Category</h2>
    <form method="post" action="{{ route('admin.categories.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
      @csrf
      <input name="name" placeholder="Name" class="bg-dark-300 border-2 border-dark-400 rounded p-2" required>
      <input name="slug" placeholder="Slug (optional)" class="bg-dark-300 border-2 border-dark-400 rounded p-2">
      <input name="sort_order" type="number" placeholder="Sort order" class="bg-dark-300 border-2 border-dark-400 rounded p-2">
      <div class="flex items-center gap-2">
        <label class="text-sm text-gray-300">Active</label>
        <input name="is_active" type="checkbox" value="1" checked>
      </div>
      <div class="md:col-span-4">
        <button class="w-full bg-gradient-to-r from-red-accent to-yellow-accent text-white rounded p-3 font-semibold">Create</button>
      </div>
    </form>
  </div>

  <div class="bg-dark-200 border-2 border-dark-300 rounded-xl p-4">
    <h2 class="text-lg font-semibold mb-3">All Categories</h2>
    <div class="space-y-3">
      @forelse($categories as $category)
      <div class="border-b border-dark-400 pb-3 mb-3">
        <form method="post" action="{{ route('admin.categories.update', $category) }}" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-center">
          @csrf
          <input name="name" value="{{ $category->name }}" class="bg-dark-300 border-2 border-dark-400 rounded p-2" required>
          <input name="slug" value="{{ $category->slug }}" class="bg-dark-300 border-2 border-dark-400 rounded p-2">
          <input name="sort_order" type="number" value="{{ $category->sort_order }}" class="bg-dark-300 border-2 border-dark-400 rounded p-2">
          <label class="flex items-center gap-2 text-sm text-gray-300"><input type="checkbox" name="is_active" value="1" {{ $category->is_active ? 'checked' : '' }}> Active</label>
          <div class="flex gap-2">
            <button class="bg-yellow-accent text-black rounded px-3 py-2 font-semibold">Save</button>
          </div>
        </form>
        <div class="mt-2">
          <form method="post" action="{{ route('admin.categories.delete', $category) }}" onsubmit="return confirm('Are you sure you want to delete this category? This will also delete all products in this category!');">
            @csrf
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white rounded px-3 py-1 text-sm font-semibold">Delete</button>
          </form>
        </div>
      </div>
      @empty
      <p class="text-gray-400">No categories yet.</p>
      @endforelse
    </div>
  </div>
</div>
@endsection


