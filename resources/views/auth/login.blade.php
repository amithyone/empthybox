@extends('layouts.app')

@section('title', 'Login - BiggestLogs')

@section('content')
<div class="min-h-[calc(100vh-200px)] flex items-center justify-center px-4 pt-16 md:pt-20 mt-[100px] pb-20 md:pb-8">
    <div class="w-full max-w-md">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl md:text-5xl font-bold mb-2 gradient-text">🔥 BiggestLogs</h1>
            <p class="text-gray-400 text-sm md:text-base">Welcome back! Login to continue</p>
        </div>

        <!-- Login Card -->
        <div class="bg-dark-200 border-2 border-dark-300 rounded-2xl shadow-2xl shadow-red-accent/10 p-6 md:p-8 backdrop-blur-sm">
            <form id="login-form">
                @csrf
                <div class="space-y-5">
                    <!-- Email Input -->
                    <div>
                        <label class="block text-sm font-medium mb-2 text-gray-300">Email Address</label>
                        <input type="email" name="email" required 
                               class="w-full bg-dark-300 border-2 border-dark-400 rounded-xl p-4 text-gray-200 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none relative z-10"
                               placeholder="your@email.com">
                    </div>
                    
                    <!-- Password Input -->
                    <div>
                        <label class="block text-sm font-medium mb-2 text-gray-300">Password</label>
                        <input type="password" name="password" required 
                               class="w-full bg-dark-300 border-2 border-dark-400 rounded-xl p-4 text-gray-200 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none relative z-10"
                               placeholder="••••••••">
                    </div>
                    
                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="remember" 
                                   class="w-4 h-4 rounded border-dark-400 bg-dark-300 text-yellow-accent focus:ring-yellow-accent focus:ring-2">
                            <span class="ml-2 text-sm text-gray-400">Remember me</span>
                        </label>
                    </div>
                    
                    <!-- Login Button -->
                    <button type="submit" id="login-btn"
                            class="w-full bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white py-4 rounded-xl font-semibold transition-all glow-button relative shadow-lg shadow-red-accent/40 hover:shadow-xl hover:shadow-yellow-accent/50 text-base md:text-lg">
                        <span class="relative z-10">Login</span>
                    </button>
                </div>
            </form>
            
            <!-- Forgot Password & Sign Up Links -->
            <div class="mt-6 space-y-3 text-center">
                <p class="text-sm text-gray-400">
                    <a href="{{ route('password.request') }}" class="text-yellow-accent font-semibold hover:text-red-accent transition">Forgot Password?</a>
                </p>
                <p class="text-sm text-gray-400">
                    Don't have an account? 
                    <a href="{{ route('register') }}" class="text-yellow-accent font-semibold hover:text-red-accent transition">Sign Up</a>
                </p>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
document.getElementById('login-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const btn = document.getElementById('login-btn');
    const btnText = btn.querySelector('span');
    btn.disabled = true;
    btnText.textContent = 'Logging in...';

    try {
        const response = await fetch('{{ route("login") }}', {
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
            showAlert(data.message, 'error');
            btn.disabled = false;
            btnText.textContent = 'Login';
        }
    } catch (error) {
        showAlert('An error occurred. Please try again.', 'error');
        btn.disabled = false;
        btnText.textContent = 'Login';
    }
});
</script>
@endsection


