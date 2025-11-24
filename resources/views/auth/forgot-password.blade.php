@extends('layouts.app')

@section('title', 'Forgot Password - BiggestLogs')

@section('content')
<div class="min-h-[calc(100vh-200px)] flex items-center justify-center px-4 pt-16 md:pt-20 pb-20 md:pb-8">
    <div class="w-full max-w-md">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl md:text-5xl font-bold mb-2 gradient-text">🔥 BiggestLogs</h1>
            <p class="text-gray-400 text-sm md:text-base">Reset your password</p>
        </div>

        <!-- Forgot Password Card -->
        <div class="bg-dark-200 border-2 border-dark-300 rounded-2xl shadow-2xl shadow-red-accent/10 p-6 md:p-8 backdrop-blur-sm">
            <form id="forgot-password-form">
                @csrf
                <div class="space-y-5">
                    <!-- Email Input -->
                    <div>
                        <label class="block text-sm font-medium mb-2 text-gray-300">Email Address</label>
                        <input type="email" name="email" required 
                               class="w-full bg-dark-300 border-2 border-dark-400 rounded-xl p-4 text-gray-200 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none"
                               placeholder="your@email.com">
                        <p class="text-xs text-gray-500 mt-1">Enter your registered email to receive a 6-digit reset code</p>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white py-4 rounded-xl font-semibold transition-all glow-button relative shadow-lg shadow-red-accent/40 hover:shadow-xl hover:shadow-yellow-accent/50 text-base md:text-lg">
                        <span class="relative z-10">Send Reset Code</span>
                    </button>
                </div>
            </form>

            <!-- Reset Password Form (Hidden Initially) -->
            <form id="reset-password-form" class="hidden space-y-5 mt-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-300">Reset Code</label>
                    <input type="text" name="code" required maxlength="6" pattern="[0-9]{6}"
                           class="w-full bg-dark-300 border-2 border-dark-400 rounded-xl p-4 text-gray-200 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none text-center text-2xl tracking-widest"
                           placeholder="000000">
                    <p class="text-xs text-gray-500 mt-1">Enter the 6-digit code sent to your email</p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-300">New Password</label>
                    <input type="password" name="password" required minlength="8"
                           class="w-full bg-dark-300 border-2 border-dark-400 rounded-xl p-4 text-gray-200 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none"
                           placeholder="Minimum 8 characters">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-300">Confirm New Password</label>
                    <input type="password" name="password_confirmation" required 
                           class="w-full bg-dark-300 border-2 border-dark-400 rounded-xl p-4 text-gray-200 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none"
                           placeholder="Re-enter new password">
                </div>
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white py-4 rounded-xl font-semibold transition-all glow-button relative shadow-lg shadow-red-accent/40 hover:shadow-xl hover:shadow-yellow-accent/50 text-base md:text-lg">
                    <span class="relative z-10">Reset Password</span>
                </button>
            </form>
            
            <!-- Back to Login -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-400">
                    Remember your password? 
                    <a href="{{ route('login') }}" class="text-yellow-accent font-semibold hover:text-red-accent transition">Login</a>
                </p>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
document.getElementById('forgot-password-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const btn = this.querySelector('button[type="submit"]');
    const btnText = btn.querySelector('span');
    btn.disabled = true;
    btnText.textContent = 'Sending...';

    try {
        const response = await fetch('{{ route("password.request") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();
        
        if (data.success) {
            showAlert(data.message, 'success');
            // Show reset password form
            document.getElementById('forgot-password-form').classList.add('hidden');
            document.getElementById('reset-password-form').classList.remove('hidden');
        } else {
            showAlert(data.message || 'Validation errors occurred', 'error');
            btn.disabled = false;
            btnText.textContent = 'Send Reset Code';
        }
    } catch (error) {
        showAlert('An error occurred. Please try again.', 'error');
        btn.disabled = false;
        btnText.textContent = 'Send Reset Code';
    }
});

document.getElementById('reset-password-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const btn = this.querySelector('button[type="submit"]');
    const btnText = btn.querySelector('span');
    btn.disabled = true;
    btnText.textContent = 'Resetting...';

    try {
        const response = await fetch('{{ route("password.reset") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();
        
        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1000);
        } else {
            showAlert(data.message || 'Validation errors occurred', 'error');
            btn.disabled = false;
            btnText.textContent = 'Reset Password';
        }
    } catch (error) {
        showAlert('An error occurred. Please try again.', 'error');
        btn.disabled = false;
        btnText.textContent = 'Reset Password';
    }
});
</script>
@endsection
