@extends('layouts.app')

@section('title', 'Register - BiggestLogs')

@section('content')
<div class="min-h-[calc(100vh-200px)] flex items-center justify-center px-4 pt-16 md:pt-20 mt-[100px] pb-20 md:pb-8">
    <div class="w-full max-w-md">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl md:text-5xl font-bold mb-2 gradient-text">🔥 BiggestLogs</h1>
            <p class="text-gray-400 text-sm md:text-base">Create your account to get started</p>
        </div>

        <!-- Register Card -->
        <div class="bg-dark-200 border-2 border-dark-300 rounded-2xl shadow-2xl shadow-red-accent/10 p-6 md:p-8 backdrop-blur-sm">
            <form id="register-form">
                @csrf
                <div class="space-y-5">
                    <!-- Full Name Input -->
                    <div>
                        <label class="block text-sm font-medium mb-2 text-gray-300">Full Name</label>
                        <input type="text" name="name" required 
                               class="w-full bg-dark-300 border-2 border-dark-400 rounded-xl p-4 text-gray-200 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none"
                               placeholder="John Doe">
                    </div>
                    
                    <!-- Email Input -->
                    <div>
                        <label class="block text-sm font-medium mb-2 text-gray-300">Email Address</label>
                        <input type="email" name="email" required 
                               class="w-full bg-dark-300 border-2 border-dark-400 rounded-xl p-4 text-gray-200 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none"
                               placeholder="your@email.com">
                    </div>
                    
                    <!-- Phone Input -->
                    <div>
                        <label class="block text-sm font-medium mb-2 text-gray-300">Phone <span class="text-xs text-gray-500">(Optional)</span></label>
                        <input type="text" name="phone" 
                               class="w-full bg-dark-300 border-2 border-dark-400 rounded-xl p-4 text-gray-200 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none"
                               placeholder="+1 (555) 123-4567">
                    </div>
                    
                    <!-- Password Input -->
                    <div>
                        <label class="block text-sm font-medium mb-2 text-gray-300">Password</label>
                        <input type="password" name="password" required minlength="8"
                               class="w-full bg-dark-300 border-2 border-dark-400 rounded-xl p-4 text-gray-200 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none"
                               placeholder="Minimum 8 characters">
                    </div>
                    
                    <!-- Confirm Password Input -->
                    <div>
                        <label class="block text-sm font-medium mb-2 text-gray-300">Confirm Password</label>
                        <input type="password" name="password_confirmation" required 
                               class="w-full bg-dark-300 border-2 border-dark-400 rounded-xl p-4 text-gray-200 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none"
                               placeholder="Re-enter password">
                    </div>
                    
                    <!-- Create Account Button -->
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white py-4 rounded-xl font-semibold transition-all glow-button relative shadow-lg shadow-red-accent/40 hover:shadow-xl hover:shadow-yellow-accent/50 text-base md:text-lg">
                        <span class="relative z-10">Create Account</span>
                    </button>
                </div>
            </form>
            
            <!-- Login Link -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-400">
                    Already have an account? 
                    <a href="{{ route('login') }}" class="text-yellow-accent font-semibold hover:text-red-accent transition">Login</a>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- PIN Setup Modal -->
<div id="pin-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[9999] hidden items-center justify-center px-4">
    <div class="bg-dark-200 border-2 border-yellow-accent rounded-2xl shadow-2xl max-w-md w-full p-6 md:p-8">
        <div class="text-center mb-6">
            <div class="text-5xl mb-3">🔒</div>
            <h2 class="text-2xl font-bold text-gray-200 mb-2">Setup Your PIN</h2>
            <p class="text-gray-400 text-sm">This PIN will be used to view your order credentials securely</p>
        </div>
        
        <form id="pin-setup-form" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-2 text-gray-300">4-6 Digit PIN</label>
                <input type="text" name="pin" required maxlength="6" pattern="[0-9]{4,6}"
                       class="w-full bg-dark-300 border-2 border-yellow-accent/50 rounded-xl p-4 text-gray-200 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none text-center text-2xl tracking-widest"
                       placeholder="0000">
                <p class="text-xs text-gray-500 mt-1">Choose a PIN between 4-6 digits</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2 text-gray-300">Confirm PIN</label>
                <input type="text" name="pin_confirmation" required maxlength="6" pattern="[0-9]{4,6}"
                       class="w-full bg-dark-300 border-2 border-yellow-accent/50 rounded-xl p-4 text-gray-200 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none text-center text-2xl tracking-widest"
                       placeholder="0000">
            </div>
            <button type="submit" 
                    class="w-full bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white py-4 rounded-xl font-semibold transition-all glow-button relative shadow-lg shadow-red-accent/40">
                <span class="relative z-10">Set PIN</span>
            </button>
        </form>
        
        <p class="text-xs text-gray-500 text-center mt-4">
            <span class="text-yellow-accent">⚠️ Remember this PIN!</span> You'll need it every time you view credentials.
        </p>
    </div>
</div>

@section('scripts')
<script>
document.getElementById('register-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Creating account...';

    try {
        const response = await fetch('{{ route("register") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();
        
        if (data.success) {
            showAlert(data.message, 'success');
            // Show PIN setup modal
            document.getElementById('pin-modal').classList.remove('hidden');
            document.getElementById('pin-modal').classList.add('flex');
        } else {
            let errorMsg = data.message || 'Validation errors occurred';
            if (data.errors) {
                errorMsg = Object.values(data.errors).flat().join(', ');
            }
            showAlert(errorMsg, 'error');
            btn.disabled = false;
            btn.textContent = 'Create Account';
        }
    } catch (error) {
        showAlert('An error occurred. Please try again.', 'error');
        btn.disabled = false;
        btn.textContent = 'Create Account';
    }
});

document.getElementById('pin-setup-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const btn = this.querySelector('button[type="submit"]');
    const btnText = btn.querySelector('span');
    btn.disabled = true;
    btnText.textContent = 'Setting PIN...';

    try {
        const response = await fetch('{{ route("setup-pin") }}', {
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
                window.location.href = '{{ route("dashboard") }}';
            }, 1000);
        } else {
            showAlert(data.message || 'Validation errors occurred', 'error');
            btn.disabled = false;
            btnText.textContent = 'Set PIN';
        }
    } catch (error) {
        showAlert('An error occurred. Please try again.', 'error');
        btn.disabled = false;
        btnText.textContent = 'Set PIN';
    }
});
</script>
@endsection


