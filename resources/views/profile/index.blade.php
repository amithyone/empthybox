@extends('layouts.app')

@section('title', 'Profile Settings - BiggestLogs')

@section('content')
<div class="max-w-2xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 py-6 md:py-8 pb-20 md:pb-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl md:text-3xl font-bold gradient-text">Profile Settings</h1>
        <button onclick="history.back()" class="text-yellow-accent hover:text-red-accent transition text-sm">← Back</button>
    </div>

    @if(session('status'))
        <div class="mb-4 p-3 rounded-lg bg-green-600/20 border border-green-500/30 text-green-300 text-sm">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-lg p-4 md:p-6">
        @csrf
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-2 text-gray-300">Email</label>
                <input type="email" value="{{ $user->email }}" class="w-full bg-dark-300 border-2 border-dark-400 text-gray-400 rounded-xl p-3 outline-none cursor-not-allowed" disabled>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2 text-gray-300">Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 outline-none" required>
                @error('name')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2 text-gray-300">New PIN (4–6 digits)</label>
                <input type="password" inputmode="numeric" name="pin" pattern="\d{4,6}" minlength="4" maxlength="6" placeholder="••••" class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 outline-none">
                @error('pin')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2 text-gray-300">Confirm PIN</label>
                <input type="password" inputmode="numeric" name="pin_confirmation" pattern="\d{4,6}" minlength="4" maxlength="6" placeholder="••••" class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 outline-none">
                @error('pin_confirmation')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="pt-4 border-t border-dark-300"></div>
            <h2 class="text-sm font-semibold text-gray-300">Change Password</h2>
            <div>
                <label class="block text-sm font-medium mb-2 text-gray-300">Current Password</label>
                <input type="password" name="current_password" placeholder="Current password" class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 outline-none">
                @error('current_password')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-2 text-gray-300">New Password</label>
                <input type="password" name="new_password" placeholder="At least 8 characters" class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 outline-none">
                @error('new_password')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-2 text-gray-300">Confirm New Password</label>
                <input type="password" name="new_password_confirmation" placeholder="Repeat new password" class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 outline-none">
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white py-3 rounded-xl font-semibold transition glow-button relative shadow-lg shadow-red-accent/40">
                    <span class="relative z-10">Save Changes</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection


