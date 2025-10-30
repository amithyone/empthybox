<?php

namespace App\Services;

use App\Services\Contracts\SmsProviderInterface;
use App\Services\Providers\SmspoolProvider;
use App\Services\Providers\TigerSmsProvider;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private $activeProvider;
    private $providers = [];

    public function __construct()
    {
        $this->initializeProviders();
        $this->setActiveProvider();
    }

    private function initializeProviders(): void
    {
        // Initialize SMSPool
        $smspoolApiKey = Setting::get('sms_smspool_api_key') ?: env('SMS_SMSPOOL_API_KEY');
        if ($smspoolApiKey && !empty(trim($smspoolApiKey))) {
            try {
                $this->providers['smspool'] = new SmspoolProvider(trim($smspoolApiKey));
            } catch (\Exception $e) {
                Log::error('Failed to initialize SMSPool provider: ' . $e->getMessage());
            }
        }

        // Initialize TigerSMS
        $tigerApiKey = Setting::get('sms_tigersms_api_key') ?: env('SMS_TIGERSMS_API_KEY');
        $tigerBaseUrl = Setting::get('sms_tigersms_base_url', null) ?: env('SMS_TIGERSMS_BASE_URL');
        if ($tigerApiKey && !empty(trim($tigerApiKey))) {
            try {
                $this->providers['tigersms'] = new TigerSmsProvider(trim($tigerApiKey), $tigerBaseUrl ? trim($tigerBaseUrl) : null);
            } catch (\Exception $e) {
                Log::error('Failed to initialize TigerSMS provider: ' . $e->getMessage());
            }
        }

        // Future providers can be added here
        // if (Setting::get('sms_twilio_api_key')) {
        //     $this->providers['twilio'] = new TwilioProvider(...);
        // }
    }

    private function setActiveProvider(): void
    {
        $activeProviderName = Setting::get('sms_active_provider', 'smspool');
        
        if ($activeProviderName === 'all') {
            $this->activeProvider = null; // special: aggregate
            return;
        }
        if (isset($this->providers[$activeProviderName])) {
            $this->activeProvider = $this->providers[$activeProviderName];
        } elseif (!empty($this->providers)) {
            // Fallback to first available provider
            $this->activeProvider = reset($this->providers);
        }
    }

    public function getActiveProvider(): ?SmsProviderInterface
    {
        return $this->activeProvider;
    }

    public function getProviders(): array
    {
        return $this->providers;
    }

    public function switchProvider(string $providerName): bool
    {
        if (isset($this->providers[$providerName])) {
            Setting::set('sms_active_provider', $providerName);
            $this->activeProvider = $this->providers[$providerName];
            Cache::forget('sms_active_provider');
            return true;
        }
        return false;
    }

    public function getBalance(): array
    {
        if ($this->activeProvider) {
            return $this->activeProvider->getBalance();
        }
        // Aggregate balances across providers when 'all'
        $out = ['success' => true, 'provider' => 'multi', 'balances' => []];
        foreach ($this->providers as $name => $provider) {
            $out['balances'][$name] = $provider->getBalance();
        }
        return $out;
    }

    public function getServices(): array
    {
        if ($this->activeProvider) {
            return $this->activeProvider->getServices();
        }
        // Aggregate across providers
        $merged = [];
        foreach ($this->providers as $name => $provider) {
            $res = $provider->getServices();
            if (($res['success'] ?? false) && isset($res['services']) && is_array($res['services'])) {
                foreach ($res['services'] as $svc) {
                    $svc['provider'] = $name;
                    $merged[] = $svc;
                }
            }
        }
        return [
            'success' => true,
            'services' => $merged,
            'provider' => 'multi',
        ];
    }

    public function getCountries(): array
    {
        if ($this->activeProvider) {
            if (method_exists($this->activeProvider, 'getCountries')) {
                return $this->activeProvider->getCountries();
            }
            return [ 'success' => false, 'error' => 'Countries not supported by this provider' ];
        }
        // Aggregate unique countries
        $set = [];
        foreach ($this->providers as $provider) {
            if (method_exists($provider, 'getCountries')) {
                $res = $provider->getCountries();
                if (($res['success'] ?? false) && isset($res['countries'])) {
                    foreach ($res['countries'] as $c) {
                        $id = (string)($c['id'] ?? '');
                        $set[$id] = $c + ['id' => $id];
                    }
                }
            }
        }
        return [ 'success' => true, 'countries' => array_values($set) ];
    }

    public function requestNumber(string $service, string $country = null, array $options = []): array
    {
        if ($this->activeProvider) {
            return $this->activeProvider->requestNumber($service, $country, $options);
        }
        // When 'all', default to first provider for purchase
        $provider = reset($this->providers);
        if ($provider) {
            return $provider->requestNumber($service, $country, $options);
        }
        return [ 'success' => false, 'error' => 'No SMS provider configured' ];
    }

    public function getMessages(string $orderId): array
    {
        if ($this->activeProvider) {
            return $this->activeProvider->getMessages($orderId);
        }
        // Not supported for multi in this context
        return [ 'success' => false, 'error' => 'Select a provider to fetch messages' ];
    }

    public function clearCache(?string $providerName = null): void
    {
        $providers = $providerName ? [$providerName] : ['smspool', 'tigersms'];
        foreach ($providers as $provider) {
            Cache::forget("sms_provider_{$provider}_balance");
            Cache::forget("sms_provider_{$provider}_services");
        }
    }

    public function testConnection(): array
    {
        if (!$this->activeProvider) {
            return [
                'success' => false,
                'message' => 'No SMS provider configured. Please add your SMSPool API key in settings.',
            ];
        }

        try {
            // Try to get balance as a connection test
            $balanceResult = $this->activeProvider->getBalance();
            
            if ($balanceResult['success']) {
                return [
                    'success' => true,
                    'message' => 'Connection successful! Balance: $' . ($balanceResult['balance'] ?? 'N/A'),
                    'provider' => $this->activeProvider->getName(),
                    'balance' => $balanceResult,
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $balanceResult['error'] ?? $balanceResult['message'] ?? 'Connection failed',
                    'provider' => $this->activeProvider->getName(),
                    'details' => $balanceResult,
                ];
            }
        } catch (\Exception $e) {
            Log::error('SMS connection test failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
                'provider' => $this->activeProvider->getName(),
            ];
        }
    }

    public function getPricing(array $params = []): array
    {
        if (!$this->activeProvider) {
            return [
                'success' => false,
                'error' => 'No SMS provider configured',
            ];
        }

        if (method_exists($this->activeProvider, 'getPricing')) {
            try {
                $data = $this->activeProvider->getPricing($params);
                return [
                    'success' => true,
                    'data' => $data,
                ];
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'success' => false,
            'error' => 'Pricing not supported by this provider',
        ];
    }
}

