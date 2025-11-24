@extends('layouts.app')

@section('title', 'SMS Service Settings - Admin')

@section('content')
<div class="max-w-4xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 pt-20 md:pt-24 pb-20 md:pb-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl md:text-3xl font-bold gradient-text">SMS Service Settings</h1>
        <a href="{{ route('admin.dashboard') }}" class="text-yellow-accent hover:text-red-accent transition">← Back</a>
    </div>

    <!-- Account Balance -->
    @if(isset($balance) && ($balance['success'] ?? false) && isset($balance['balance']))
    <div class="bg-gradient-to-r from-red-accent via-yellow-accent to-red-accent rounded-xl shadow-2xl shadow-red-accent/30 p-6 md:p-8 text-white mb-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm md:text-base mb-2 opacity-90">SMS Account Balance</p>
                <p class="text-3xl md:text-4xl font-bold">{{ number_format($balance['balance'], 2) }} {{ $balance['currency'] ?? 'USD' }}</p>
                <p class="text-xs md:text-sm mt-2 opacity-80">{{ $balance['provider'] ?? 'Provider' }}</p>
            </div>
            <div class="text-4xl md:text-5xl">💰</div>
        </div>
    </div>
    @endif

    <form id="sms-settings-form">
        @csrf
        
        <!-- SMS Coming Soon -->
        <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-lg p-4 md:p-6 mb-6">
            <h2 class="text-lg md:text-xl font-bold mb-4 text-gray-200">SMS Service Availability</h2>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-gray-300 font-medium">Mark SMS as Coming Soon</label>
                        <p class="text-xs text-gray-500 mt-1">Hide SMS from regular users. Admins can still access.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="sms_coming_soon" value="1" 
                               class="sr-only peer" {{ $settings['sms_coming_soon'] ?? false ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-dark-400 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-yellow-accent rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-red-accent peer-checked:to-yellow-accent"></div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Provider Selection -->
        <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-lg p-4 md:p-6 mb-6">
            <h2 class="text-lg md:text-xl font-bold mb-4 text-gray-200">Active Provider</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="flex items-center space-x-3">
                    <input type="radio" name="sms_active_provider" value="smspool" class="form-radio"
                           {{ $activeProvider === 'smspool' ? 'checked' : '' }}>
                    <span class="text-gray-300">SMSPool</span>
                </label>
                <label class="flex items-center space-x-3">
                    <input type="radio" name="sms_active_provider" value="tigersms" class="form-radio"
                           {{ $activeProvider === 'tigersms' ? 'checked' : '' }}>
                    <span class="text-gray-300">TigerSMS</span>
                </label>
            </div>
        </div>

        <!-- SMSPool Configuration -->
        <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-lg p-4 md:p-6 mb-6">
            <h2 class="text-lg md:text-xl font-bold mb-4 text-gray-200">SMSPool Configuration</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-300">API Key</label>
                    <input type="text" name="sms_smspool_api_key" value="{{ $settings['sms_smspool_api_key'] }}"
                           class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none font-mono text-sm"
                           placeholder="Enter SMSPool API Key">
                    <p class="text-xs text-gray-500 mt-1">Get your API key from <a href="https://smspool.net" target="_blank" class="text-yellow-accent hover:underline">smspool.net</a></p>
                </div>

                <p class="text-xs text-gray-500">Currently using: {{ ucfirst($activeProvider) }}</p>

                <button type="button" onclick="testConnection()" 
                        class="w-full bg-dark-300 border-2 border-dark-400 text-gray-300 py-3 rounded-xl font-semibold transition hover:bg-dark-400 hover:border-yellow-accent/50">
                    Test Connection
                </button>
            </div>
        </div>

        <!-- TigerSMS Configuration -->
        <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-lg p-4 md:p-6 mb-6">
            <h2 class="text-lg md:text-xl font-bold mb-4 text-gray-200">TigerSMS Configuration</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-300">API Key</label>
                    <input type="text" name="sms_tigersms_api_key" value="{{ $settings['sms_tigersms_api_key'] ?? '' }}"
                           class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none font-mono text-sm"
                           placeholder="Enter TigerSMS API Key">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-300">Base URL (optional)</label>
                    <input type="text" name="sms_tigersms_base_url" value="{{ $settings['sms_tigersms_base_url'] ?? '' }}"
                           class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none font-mono text-sm"
                           placeholder="https://api.tigersms.net">
                </div>

                <button type="button" onclick="testTigersmsConnection()" 
                        class="w-full bg-dark-300 border-2 border-dark-400 text-gray-300 py-3 rounded-xl font-semibold transition hover:bg-dark-400 hover:border-yellow-accent/50">
                    Test TigerSMS Connection
                </button>
            </div>
        </div>

        <!-- Available Services -->
        @if(isset($services) && $services['success'] && count($services['services']) > 0)
        <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-lg p-4 md:p-6 mb-6">
            <h2 class="text-lg md:text-xl font-bold mb-4 text-gray-200">Available Services ({{ count($services['services']) }})</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 max-h-96 overflow-y-auto">
                @foreach(array_slice($services['services'], 0, 20) as $service)
                <div class="bg-dark-300 border border-dark-400 p-3 rounded-lg">
                    <div class="font-semibold text-gray-200 text-sm">{{ $service['name'] }}</div>
                    <div class="text-xs text-gray-400 mt-1">
                        <span>Country: {{ $service['country'] }}</span> | 
                        <span class="text-yellow-accent">${{ number_format($service['price'], 3) }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @if(count($services['services']) > 20)
            <p class="text-xs text-gray-500 mt-3">Showing first 20 services. Total: {{ count($services['services']) }} services.</p>
            @endif
        </div>
        @endif

        <button type="submit" 
                class="w-full bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white py-4 rounded-xl font-semibold transition glow-button relative shadow-lg shadow-red-accent/40">
            <span class="relative z-10">Save SMS Settings</span>
        </button>
    </form>
</div>

@section('scripts')
<script>
document.getElementById('sms-settings-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    // Convert checkbox
    formData.set('sms_coming_soon', this.querySelector('[name="sms_coming_soon"]').checked ? '1' : '0');
    
    const btn = this.querySelector('button[type="submit"]');
    const btnText = btn.querySelector('span');
    btn.disabled = true;
    btnText.textContent = 'Saving...';
    
    try {
        const response = await fetch('{{ route("admin.sms-settings.update") }}', {
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
            showAlert(data.message || 'Failed to update SMS settings', 'error');
            btn.disabled = false;
            btnText.textContent = 'Save SMS Settings';
        }
    } catch (error) {
        showAlert('An error occurred. Please try again.', 'error');
        btn.disabled = false;
        btnText.textContent = 'Save SMS Settings';
    }
});

async function testConnection() {
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    formData.append('test_connection', '1');
    formData.append('sms_smspool_api_key', document.querySelector('[name="sms_smspool_api_key"]').value);
    
    const btn = event.target;
    btn.disabled = true;
    btn.textContent = 'Testing...';
    
    try {
        const response = await fetch('{{ route("admin.sms-settings.update") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('✅ Connection successful! ' + data.test_result.message, 'success');
        } else {
            showAlert('❌ Connection failed: ' + (data.message || 'Please check your API key'), 'error');
        }
    } catch (error) {
        showAlert('An error occurred while testing connection.', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Test Connection';
    }
}

async function testTigersmsConnection() {
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    formData.append('test_connection', '1');
    formData.append('sms_tigersms_api_key', document.querySelector('[name="sms_tigersms_api_key"]').value);
    formData.append('sms_tigersms_base_url', document.querySelector('[name="sms_tigersms_base_url"]').value || '');
    
    const btn = event.target;
    btn.disabled = true;
    btn.textContent = 'Testing...';
    
    try {
        const response = await fetch('{{ route("admin.sms-settings.update") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('✅ TigerSMS Connection successful! ' + data.test_result.message, 'success');
        } else {
            showAlert('❌ TigerSMS Connection failed: ' + (data.message || 'Please check your API key'), 'error');
        }
    } catch (error) {
        showAlert('An error occurred while testing TigerSMS connection.', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Test TigerSMS Connection';
    }
}
</script>
@endsection

