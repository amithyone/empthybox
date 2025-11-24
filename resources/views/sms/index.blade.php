@extends('layouts.app')

@section('title', 'SMS Verification Service - BiggestLogs')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 pt-16 md:pt-20 mt-[100px] pb-20 md:pb-8">
    <div class="flex items-center justify-between mb-4 md:mb-6">
        <h1 class="text-2xl md:text-3xl font-bold gradient-text">📱 SMS Verification Service</h1>
        <a href="{{ route('dashboard') }}" class="text-yellow-accent hover:text-red-accent transition flex items-center gap-2">
            <span>← Back</span>
        </a>
    </div>


    <!-- Request SMS Number -->
    <div id="sms-data" data-services='@json($services["services"] ?? [])' data-countries='@json($countries["countries"] ?? [])' class="hidden"></div>
    
    <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-lg p-4 md:p-6 mb-6">
        <h2 class="text-lg md:text-xl font-bold mb-4 text-gray-200">Request SMS Number</h2>
        
        @if(isset($services['success']) && !$services['success'])
        <div class="bg-red-500/20 border-2 border-red-500/50 rounded-xl p-4 mb-4">
            <p class="text-red-300 font-semibold">⚠️ Error Loading Services</p>
            <p class="text-sm text-red-200 mt-1">{{ $services['error'] ?? 'Unknown error occurred' }}</p>
            <p class="text-xs text-red-300 mt-2">Provider: {{ $services['provider'] ?? 'Unknown' }}</p>
        </div>
        @endif
        
        @if(isset($countries['success']) && !$countries['success'])
        <div class="bg-yellow-500/20 border-2 border-yellow-500/50 rounded-xl p-4 mb-4">
            <p class="text-yellow-300 font-semibold">⚠️ Warning: Countries may not be available</p>
            <p class="text-sm text-yellow-200 mt-1">{{ $countries['error'] ?? 'Unknown error occurred' }}</p>
        </div>
        @endif
        
        @if(isset($services['success']) && $services['success'] && empty($services['services']))
        <div class="bg-yellow-500/20 border-2 border-yellow-500/50 rounded-xl p-4 mb-4">
            <p class="text-yellow-300 font-semibold">⚠️ No Services Available</p>
            <p class="text-sm text-yellow-200 mt-1">The provider did not return any services. Please check your API key and try again.</p>
        </div>
        @endif
        
        <form id="sms-request-form">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-300">Service</label>
                    <div class="relative">
                        <input type="text" id="service-search" placeholder="Click to search services (WhatsApp, Facebook, Google...)" 
                               class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none cursor-pointer"
                               autocomplete="off" readonly>
                        <select name="service" id="service-select"
                                class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none hidden">
                            <option value="">Select a service...</option>
                        </select>
                        <input type="hidden" name="service_id" id="service-id-input">
                        <div id="service-results" class="absolute z-50 w-full bg-dark-300 border-2 border-dark-400 rounded-xl mt-1 max-h-96 overflow-y-auto shadow-2xl hidden"></div>
                        <div id="service-country-panel" class="mt-2 bg-dark-300 border-2 border-dark-400 rounded-xl p-3 hidden"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Click the field to see all available services</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-300">Country (Optional)</label>
                    <div class="relative">
                        <input type="text" id="country-search" placeholder="Click to search countries..." 
                               class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none cursor-pointer"
                               autocomplete="off" readonly>
                        <input type="hidden" name="country" id="country-input">
                        <div id="country-results" class="absolute z-50 w-full bg-dark-300 border-2 border-dark-400 rounded-xl mt-1 max-h-96 overflow-y-auto shadow-2xl hidden"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Click the field to see all available countries</p>
                    <div id="country-service-pricing-panel" class="mt-2 bg-dark-300 border-2 border-dark-400 rounded-xl p-3 hidden"></div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-300">Max Price (USD, Optional)</label>
                    <input type="number" name="max_price" step="0.01" min="0" placeholder="e.g. 1.50"
                           class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none">
                    <p class="text-xs text-gray-500 mt-1">We'll only request numbers at or below this price.</p>
                </div>

                <button type="submit" 
                        class="w-full bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white py-3 rounded-xl font-semibold transition glow-button relative shadow-lg shadow-red-accent/40">
                    <span class="relative z-10">Request Number</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Active SMS Orders -->
    <div id="active-orders" class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-lg p-4 md:p-6 mb-6 hidden">
        <h2 class="text-lg md:text-xl font-bold mb-4 text-gray-200">Active SMS Orders</h2>
        <div id="orders-list" class="space-y-3">
            <!-- Orders will be displayed here -->
        </div>
    </div>

</div>

@section('scripts')
<script>
let activeOrders = [];
const smsDataEl = document.getElementById('sms-data');
let allServices = [];
let allCountries = [];
try { allServices = JSON.parse(smsDataEl?.dataset?.services || '[]'); } catch (e) { allServices = []; }
try { allCountries = JSON.parse(smsDataEl?.dataset?.countries || '[]'); } catch (e) { allCountries = []; }

// Service Search
const serviceSearch = document.getElementById('service-search');
const serviceSelect = document.getElementById('service-select');
const serviceResults = document.getElementById('service-results');

let serviceFilter = '';

// Show all services when clicking
serviceSearch.addEventListener('focus', function() {
    this.removeAttribute('readonly');
    displayServices();
});

serviceSearch.addEventListener('input', function() {
    serviceFilter = this.value.toLowerCase();
    displayServices();
});

function displayServices() {
    let filtered = allServices;
    
    if (serviceFilter.length > 0) {
        filtered = allServices.filter(s => 
            s.name.toLowerCase().includes(serviceFilter)
        );
    }
    
    if (filtered.length === 0) {
        serviceResults.innerHTML = '<div class="p-3 text-gray-400 text-sm text-center">No services found</div>';
        serviceResults.classList.remove('hidden');
        return;
    }
    
    // Show first 50 services
    const displayServices = filtered.slice(0, 50);
    
    serviceResults.innerHTML = displayServices.map(s => `
        <div class="p-3 hover:bg-dark-400 cursor-pointer border-b border-dark-400 last:border-0 transition ${s.popular ? 'bg-yellow-accent/5' : ''}" 
             onclick="selectServiceOption('${s.id}', '${s.name.replace(/'/g, "\\'").replace(/"/g, '&quot;')}', ${s.price})">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="font-semibold text-gray-200">${s.name} ${s.price && s.price > 0 ? `<span class=\"text-green-400 font-bold\">- $${parseFloat(s.price).toFixed(2)}</span>` : `<span class=\"text-gray-400\">- Price: N/A</span>`} ${s.popular ? '⭐' : ''}</div>
                    ${s.price && s.price > 0 ? `<div class=\"text-xs text-gray-400 mt-1\">Price: $${parseFloat(s.price).toFixed(2)}</div>` : `<div class=\"text-xs text-gray-400 mt-1\">Price: N/A</div>`}
                </div>
                <div class=\"ml-3 text-right\">
                    ${s.count !== undefined && s.count > 0 ? `<div class=\"text-xs text-gray-400\">${s.count} available</div>` : ''}
                    <button type=\"button\" class=\"mt-2 text-xs text-yellow-accent hover:text-red-accent\" onclick=\"event.stopPropagation(); previewServicePricing('${s.id}', '${s.name.replace(/'/g, "\\'").replace(/\"/g, '&quot;')}')\">Countries</button>
                </div>
            </div>
        </div>
    `).join('') + (filtered.length > 50 ? `<div class="p-2 text-center text-xs text-gray-500 border-t border-dark-400">Showing 50 of ${filtered.length} services. Type to filter more.</div>` : '');
    serviceResults.classList.remove('hidden');
}

function selectServiceOption(id, name, price) {
    serviceSelect.value = id;
    document.getElementById('service-id-input').value = id;
    const priceText = (price && !isNaN(price)) ? ` - $${parseFloat(price).toFixed(2)}` : '';
    serviceSearch.value = `${name}${priceText}`;
    serviceSearch.setAttribute('readonly', 'readonly');
    serviceResults.classList.add('hidden');
    
    // Clear country selection when service changes
    countryInput.value = '';
    countrySearch.value = '';
    availableCountriesForService = null;
    
    // Load available countries for this service
    loadAvailableCountriesForService(id);
    
    previewServicePricing(id, name);
}

// Country Search
const countrySearch = document.getElementById('country-search');
const countryInput = document.getElementById('country-input');
const countryResults = document.getElementById('country-results');

let countryFilter = '';
let availableCountriesForService = null; // Store filtered countries for selected service

// Show all countries when clicking
countrySearch.addEventListener('focus', function() {
    this.removeAttribute('readonly');
    displayCountries();
});

countrySearch.addEventListener('input', function() {
    countryFilter = this.value.toLowerCase();
    displayCountries();
});

function displayCountries() {
    // Use filtered countries if service is selected, otherwise use all countries
    let countriesToShow = availableCountriesForService || allCountries;
    let filtered = countriesToShow;
    
    if (countryFilter.length > 0) {
        filtered = countriesToShow.filter(c => 
            (c.name && c.name.toLowerCase().includes(countryFilter)) ||
            (c.code && c.code.toLowerCase().includes(countryFilter))
        );
    }
    
    if (filtered.length === 0) {
        if (availableCountriesForService) {
            countryResults.innerHTML = '<div class="p-3 text-gray-400 text-sm text-center">No countries available for this service</div>';
        } else {
            countryResults.innerHTML = '<div class="p-3 text-gray-400 text-sm text-center">No countries found</div>';
        }
        countryResults.classList.remove('hidden');
        return;
    }
    
    countryResults.innerHTML = filtered.map(c => `
        <div class="p-3 hover:bg-dark-400 cursor-pointer border-b border-dark-400 last:border-0 transition" 
             onclick="selectCountryOption('${c.id}', '${(c.name || '').replace(/'/g, "\\'").replace(/"/g, '&quot;')}', '${c.code || ''}')">
            <div class="font-semibold text-gray-200">${c.name || 'Unknown'}</div>
            ${c.code ? `<div class="text-xs text-gray-400 mt-1">Code: ${c.code}</div>` : ''}
        </div>
    `).join('');
    countryResults.classList.remove('hidden');
}

async function loadAvailableCountriesForService(serviceId) {
    try {
        // Show loading indicator
        const countryResults = document.getElementById('country-results');
        if (!countryResults.classList.contains('hidden')) {
            countryResults.innerHTML = '<div class="p-3 text-gray-400 text-sm text-center">Loading available countries...</div>';
        }
        
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        formData.append('service', serviceId);
        
        const response = await fetch('{{ route("sms.service-countries") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        
        if (data.success && Array.isArray(data.countries)) {
            availableCountriesForService = data.countries;
            
            // Show status message
            if (availableCountriesForService.length > 0) {
                showAlert(`✓ ${availableCountriesForService.length} countries available for this service`, 'success', 3000);
            } else {
                showAlert('⚠ No countries available for this service. You may need to select a different service.', 'warning', 5000);
            }
            
            // Clear country selection if current selection is not available
            if (countryInput.value) {
                const isAvailable = availableCountriesForService.some(c => c.id === countryInput.value);
                if (!isAvailable) {
                    countryInput.value = '';
                    countrySearch.value = '';
                    showAlert('The previously selected country is not available for this service. Please select a country from the filtered list.', 'warning', 4000);
                }
            }
            // Refresh display if dropdown is open
            if (!countryResults.classList.contains('hidden')) {
                displayCountries();
            }
        } else {
            // Fallback to all countries if service-specific lookup fails
            availableCountriesForService = null;
            if (!countryResults.classList.contains('hidden')) {
                displayCountries();
            }
        }
    } catch (error) {
        availableCountriesForService = null;
        if (!countryResults.classList.contains('hidden')) {
            displayCountries();
        }
    }
}

function selectCountryOption(id, name, code) {
    countryInput.value = id;
    countrySearch.value = `${name}${code ? ' (' + code + ')' : ''}`;
    countrySearch.setAttribute('readonly', 'readonly');
    countryResults.classList.add('hidden');
    // Fetch country-specific pricing and update displayed prices
    refreshCountryPricing(id);
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!serviceSearch.contains(e.target) && !serviceResults.contains(e.target)) {
        serviceResults.classList.add('hidden');
    }
    if (!countrySearch.contains(e.target) && !countryResults.contains(e.target)) {
        countryResults.classList.add('hidden');
    }
});

async function refreshCountryPricing(countryId) {
    try {
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        formData.append('country', countryId);
        const response = await fetch('{{ route("sms.pricing") }}', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json();
        if (data && data.success && Array.isArray(data.data)) {
            // Build serviceId -> minPrice map from returned rows
            const map = {};
            for (const row of data.data) {
                const sid = (row.service_id || row.service || row.id);
                let p = (row.price ?? row.Price ?? row.cost ?? row.Cost ?? row.amount);
                if (sid == null || p == null) continue;
                if (typeof p === 'string') { p = p.replace(/[^0-9.]/g, ''); }
                if (!p || isNaN(p)) continue;
                p = parseFloat(p);
                if (map[sid] == null || p < map[sid]) map[sid] = p;
            }
            // Update allServices prices if available
            allServices = allServices.map(s => {
                const sid = s.id != null ? String(s.id) : null;
                if (sid && map[sid] != null) {
                    return { ...s, price: map[sid] };
                }
                return s;
            });
            // If dropdown open, re-render
            displayServices();
        }
    } catch (err) {
        // Pricing refresh failed silently
    }
}

async function previewServicePricing(serviceId, serviceName) {
    try {
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        formData.append('service', serviceId);
        const response = await fetch('{{ route("sms.pricing") }}', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json();
        // Render under the country field as requested
        const panel = document.getElementById('country-service-pricing-panel');
        if (!data || !data.success || !Array.isArray(data.data) || data.data.length === 0) {
            panel.classList.remove('hidden');
            panel.innerHTML = `<div class="text-sm text-gray-400">No country pricing found for ${serviceName || 'service'}.</div>`;
            return;
        }
        // Aggregate per country: get min and max
        const byCountry = {};
        for (const row of data.data) {
            const countryId = row.country_id || row.country || row.countryID || row.countryCode || row.country_code;
            if (countryId == null) continue;
            let p = (row.price ?? row.Price ?? row.cost ?? row.Cost ?? row.amount);
            if (typeof p === 'string') { p = p.replace(/[^0-9.]/g, ''); }
            if (!p || isNaN(p)) continue;
            p = parseFloat(p);
            const key = String(countryId);
            if (!byCountry[key]) byCountry[key] = { min: p, max: p, count: 1 };
            else {
                byCountry[key].min = Math.min(byCountry[key].min, p);
                byCountry[key].max = Math.max(byCountry[key].max, p);
                byCountry[key].count += 1;
            }
        }
        // Map to objects with code/name, then filter to top 6 countries
        const preferredCodes = new Set(['US','GB','UK','EN','DE','CA','AU','FR']);
        const preferredNames = new Set(['United States','United Kingdom','England','Germany','Canada','Australia','France']);
        const rowsAll = Object.entries(byCountry).map(([cid, stats]) => {
            const cObj = allCountries.find(c => String(c.id) === String(cid));
            const code = (cObj && cObj.code) ? cObj.code : cid;
            const name = (cObj && cObj.name) ? cObj.name : 'Unknown';
            return { cid, code, name, min: stats.min, max: stats.max };
        });
        const rowsFiltered = rowsAll.filter(r => preferredCodes.has(String(r.code).toUpperCase()) || preferredNames.has(String(r.name)));
        // Ensure GB/UK/England treated as one; dedupe by canonical country name
        const canonicalName = (r) => {
            const code = String(r.code).toUpperCase();
            if (code === 'GB' || code === 'UK' || r.name === 'England' || r.name === 'United Kingdom') return 'United Kingdom';
            if (code === 'US' || r.name === 'United States') return 'United States';
            if (code === 'DE' || r.name === 'Germany') return 'Germany';
            if (code === 'CA' || r.name === 'Canada') return 'Canada';
            if (code === 'AU' || r.name === 'Australia') return 'Australia';
            if (code === 'FR' || r.name === 'France') return 'France';
            return r.name || code;
        };
        const merged = {};
        for (const r of rowsFiltered) {
            const key = canonicalName(r);
            if (!merged[key]) merged[key] = { name: key, code: r.code, min: r.min, max: r.max };
            else {
                merged[key].min = Math.min(merged[key].min, r.min);
                merged[key].max = Math.max(merged[key].max, r.max);
            }
        }
        const rows = Object.values(merged).sort((a, b) => a.min - b.min).slice(0, 6);

        const list = rows.map(r => `
            <button type="button" class="w-full text-left px-2 py-2 border-b border-dark-400 last:border-0 hover:bg-dark-400 rounded" onclick="selectCountryFromPreview('${r.name.replace(/'/g, "\\'").replace(/\"/g, '&quot;')}', '${String(r.code).replace(/'/g, "\\'").replace(/\"/g, '&quot;')}')">
                <div class="flex items-center justify-between">
                    <div class="text-gray-200 text-sm">${r.name} <span class="text-xs text-gray-500">(${r.code})</span></div>
                    <div class="text-xs ${r.min <= 2 ? 'text-green-400' : 'text-yellow-accent'}">$${r.min.toFixed(2)}${r.max && r.max !== r.min ? ` - $${r.max.toFixed(2)}` : ''}</div>
                </div>
            </button>
        `).join('');

        panel.classList.remove('hidden');
        panel.innerHTML = `
            <div class="text-sm text-gray-300 font-semibold mb-2">${serviceName || 'Service'} • Top Countries & Prices</div>
            ${list || '<div class="text-sm text-gray-400">No pricing available.</div>'}
        `;
    } catch (e) {
        const panel = document.getElementById('country-service-pricing-panel');
        panel.classList.remove('hidden');
        panel.innerHTML = `<div class="text-sm text-red-400">Failed to load country pricing.</div>`;
    }
}

function selectCountryFromPreview(countryName, countryCode) {
    try {
        const found = allCountries.find(c =>
            (c.code && String(c.code).toUpperCase() === String(countryCode).toUpperCase()) ||
            (c.name && String(c.name) === String(countryName))
        );
        if (found) {
            selectCountryOption(found.id, found.name || countryName, found.code || countryCode);
        } else {
            const countryInputEl = document.getElementById('country-input');
            const countrySearchEl = document.getElementById('country-search');
            countryInputEl.value = '';
            countrySearchEl.value = `${countryName}${countryCode ? ' (' + countryCode + ')' : ''}`;
        }
    } finally {
        const panel = document.getElementById('country-service-pricing-panel');
        panel.classList.add('hidden');
    }
}

function selectService(serviceId, country, price) {
    const service = allServices.find(s => s.id == serviceId);
    if (service) {
        selectServiceOption(serviceId, service.name, service.price);
    }
    if (country) {
        countryInput.value = country;
        countrySearch.value = country;
    }
}

document.getElementById('sms-request-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    const btn = this.querySelector('button[type="submit"]');
    const btnText = btn.querySelector('span');
    btn.disabled = true;
    btnText.textContent = 'Requesting...';
    
    // Validate service is selected
    const serviceId = serviceSelect.value || document.getElementById('service-id-input').value;
    if (!serviceId) {
        showAlert('Please select a service', 'error');
        btn.disabled = false;
        btnText.textContent = 'Request Number';
        return;
    }
    
    // Ensure service value is in formData
    if (!formData.get('service')) {
        formData.set('service', serviceId);
    }
    
    // Ensure country is set correctly
    const countryId = countryInput.value;
    if (countryId && countryId !== 'null' && countryId !== '') {
        formData.set('country', countryId);
    } else {
        formData.delete('country');
    }
    
    try {
        const response = await fetch('{{ route("sms.request-number") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.message || 'SMS number requested successfully! Redirecting to inbox...', 'success');
            
            // Redirect to inbox after 2 seconds
            setTimeout(() => {
                window.location.href = '{{ route("sms.inbox") }}';
            }, 2000);
        } else {
            showAlert(data.error || data.message || 'Failed to request SMS number', 'error');
        }
    } catch (error) {
        showAlert('An error occurred. Please try again.', 'error');
    } finally {
        btn.disabled = false;
        btnText.textContent = 'Request Number';
    }
});

function updateActiveOrders() {
    const container = document.getElementById('active-orders');
    const list = document.getElementById('orders-list');
    
    if (activeOrders.length === 0) {
        container.classList.add('hidden');
        return;
    }
    
    container.classList.remove('hidden');
    list.innerHTML = activeOrders.map(order => `
        <div class="bg-dark-300 border border-dark-400 p-4 rounded-lg" id="order-${order.order_id}">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <div class="font-semibold text-gray-200">${order.number || 'Waiting...'}</div>
                    <div class="text-xs text-gray-400">Order: ${order.order_id}</div>
                    <div class="text-xs text-gray-400">Service: ${order.service}</div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs ${order.status === 'received' ? 'bg-green-600/20 text-green-400 border border-green-500/30' : 'bg-yellow-accent/20 text-yellow-accent border border-yellow-accent/30'}">
                    ${order.status === 'received' ? 'Received' : 'Waiting'}
                </span>
            </div>
            ${order.code ? `
                <div class="mt-3 p-3 bg-green-600/20 border border-green-500/30 rounded-lg">
                    <p class="text-xs text-gray-400 mb-1">Verification Code:</p>
                    <p class="text-xl font-bold text-green-400 font-mono">${order.code}</p>
                </div>
            ` : ''}
        </div>
    `).join('');
}

function pollForMessages(orderId) {
    const interval = setInterval(async () => {
        try {
            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            formData.append('order_id', orderId);
            
            const response = await fetch('{{ route("sms.check-status") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (data.success && data.messages && data.messages.length > 0) {
                const order = activeOrders.find(o => o.order_id === orderId);
                if (order) {
                    order.code = data.messages[0].code;
                    order.status = 'received';
                    updateActiveOrders();
                    showAlert('SMS code received!', 'success');
                    clearInterval(interval);
                }
            }
            
            if (data.status === 'expired' || data.status === 'cancelled') {
                clearInterval(interval);
            }
        } catch (error) {
            // Polling error - will retry on next interval
        }
    }, 5000); // Poll every 5 seconds
    
    // Stop polling after 5 minutes
    setTimeout(() => clearInterval(interval), 300000);
}
</script>
@endsection

