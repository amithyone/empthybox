@extends('layouts.app')

@section('title', 'Settings - Admin')

@section('content')
<div class="max-w-4xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 pt-16 md:pt-20 mt-[100px] pb-20 md:pb-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl md:text-3xl font-bold gradient-text">Settings</h1>
        <a href="{{ route('admin.dashboard') }}" class="text-yellow-accent hover:text-red-accent transition">← Back</a>
    </div>

    <form id="settings-form">
        @csrf
        
        <!-- Maintenance Mode -->
        <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-lg p-4 md:p-6 mb-6">
            <h2 class="text-lg md:text-xl font-bold mb-4 text-gray-200">Maintenance Mode</h2>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-gray-300 font-medium">Enable Maintenance Mode</label>
                        <p class="text-xs text-gray-500 mt-1">Put the site in maintenance mode for manual payment processing</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="maintenance_mode" value="1" 
                               class="sr-only peer" {{ $settings['maintenance_mode'] ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-dark-400 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-yellow-accent rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-red-accent peer-checked:to-yellow-accent"></div>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-300">Maintenance Message</label>
                    <textarea name="maintenance_message" rows="3" 
                              class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none resize-none">{{ $settings['maintenance_message'] }}</textarea>
                </div>
            </div>
        </div>

        <!-- General Settings -->
        <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-lg p-4 md:p-6 mb-6">
            <h2 class="text-lg md:text-xl font-bold mb-4 text-gray-200">General Settings</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-300">Site Name</label>
                    <input type="text" name="site_name" value="{{ $settings['site_name'] }}"
                           class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-300">Site Email</label>
                    <input type="email" name="site_email" value="{{ $settings['site_email'] }}"
                           class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none">
                </div>
            </div>
        </div>

        <!-- Manual Payment Settings -->
        <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-lg p-4 md:p-6 mb-6">
            <h2 class="text-lg md:text-xl font-bold mb-4 text-gray-200">Manual Payment</h2>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-gray-300 font-medium">Enable Manual Payment</label>
                        <p class="text-xs text-gray-500 mt-1">Allow users to pay manually</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="manual_payment_enabled" value="1" 
                               class="sr-only peer" {{ $settings['manual_payment_enabled'] ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-dark-400 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-yellow-accent rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-red-accent peer-checked:to-yellow-accent"></div>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-300">Payment Instructions</label>
                    <textarea name="manual_payment_instructions" rows="4" 
                              class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none resize-none">{{ $settings['manual_payment_instructions'] }}</textarea>
                </div>
            </div>
        </div>

        <button type="submit" 
                class="w-full bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white py-4 rounded-xl font-semibold transition glow-button relative shadow-lg shadow-red-accent/40">
            <span class="relative z-10">Save Settings</span>
        </button>
    </form>
</div>

@section('scripts')
<script>
document.getElementById('settings-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    // Convert checkboxes
    formData.set('maintenance_mode', this.querySelector('[name="maintenance_mode"]').checked ? '1' : '0');
    formData.set('manual_payment_enabled', this.querySelector('[name="manual_payment_enabled"]').checked ? '1' : '0');
    
    const btn = this.querySelector('button[type="submit"]');
    const btnText = btn.querySelector('span');
    btn.disabled = true;
    btnText.textContent = 'Saving...';
    
    try {
        const response = await fetch('{{ route("admin.settings.update") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.message || 'Failed to update settings', 'error');
            btn.disabled = false;
            btnText.textContent = 'Save Settings';
        }
    } catch (error) {
        showAlert('An error occurred. Please try again.', 'error');
        btn.disabled = false;
        btnText.textContent = 'Save Settings';
    }
});
</script>
@endsection

