<?php

namespace App\Services\Providers;

use App\Services\Contracts\SmsProviderInterface;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SmspoolProvider implements SmsProviderInterface
{
    private $apiKey;
    private $baseUrl = 'https://api.smspool.net/stubs/handler_api.php';

    public function __construct($apiKey)
    {
        if (empty($apiKey)) {
            throw new \InvalidArgumentException('SMSPool API key is required');
        }
        $this->apiKey = $apiKey;
    }

    public function getName(): string
    {
        return 'SMSPool';
    }

    public function validateConnection(): bool
    {
            try {
                $response = $this->makeRequest('getBalance');
            // Check multiple possible success indicators
            if (isset($response['success']) && $response['success'] === true) {
                return true;
            }
            // Sometimes SMSPool returns balance directly
            if (isset($response['balance']) || isset($response['credits']) || isset($response['amount'])) {
                return true;
            }
            // Check for error in response
            if (isset($response['error']) || isset($response['message'])) {
                Log::error('SMSPool validation error: ' . ($response['error'] ?? $response['message'] ?? 'Unknown error'));
            }
            return false;
        } catch (\Exception $e) {
            Log::error('SMSPool connection validation failed: ' . $e->getMessage());
            return false;
        }
    }

    public function getBalance(): array
    {
        try {
            $cacheKey = 'sms_provider_smspool_balance';
            return Cache::remember($cacheKey, 60, function () {
                $response = $this->makeRequest('getBalance');
                
                // Handle different response formats
                if (isset($response['success']) && $response['success']) {
                    return [
                        'success' => true,
                        'balance' => $response['balance'] ?? $response['credits'] ?? 0,
                        'currency' => $response['currency'] ?? 'USD',
                        'provider' => $this->getName(),
                    ];
                } elseif (isset($response['balance']) || isset($response['credits'])) {
                    // Some APIs return data without success flag
                    return [
                        'success' => true,
                        'balance' => $response['balance'] ?? $response['credits'] ?? 0,
                        'currency' => $response['currency'] ?? 'USD',
                        'provider' => $this->getName(),
                    ];
                }
                
                return [
                    'success' => false,
                    'error' => $response['message'] ?? $response['error'] ?? 'Failed to fetch balance',
                    'raw_response' => $response,
                ];
            });
        } catch (\Exception $e) {
            Log::error('SMSPool balance fetch failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getServices(): array
    {
        try {
            $cacheKey = 'sms_provider_smspool_services';
            return Cache::remember($cacheKey, 3600, function () {
                // Use SMSPool API: GET to /service/retrieve_all (correct endpoint)
                $restUrl = 'https://api.smspool.net/service/retrieve_all';
                
                Log::info("SMSPool trying GET API for services", ['url' => $restUrl, 'method' => 'GET']);
                
                // Use cURL to call REST API with GET
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $restUrl . '?key=' . urlencode($this->apiKey));
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Accept: application/json',
                ]);
                
                $restResponse = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                curl_close($ch);
                
                Log::info("SMSPool REST API response", [
                    'http_code' => $httpCode,
                    'error' => $error,
                    'response' => substr($restResponse, 0, 2000),
                ]);
                
                if ($httpCode === 200 && $restResponse) {
                    $data = json_decode($restResponse, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                        // Log sample service to see structure
                        if (!empty($data)) {
                            $sample = is_array($data[0] ?? reset($data)) ? (reset($data)) : null;
                            Log::info("SMSPool service sample structure", [
                    'sample' => $sample,
                    'sample_keys' => $sample ? array_keys($sample) : [],
                    'has_price' => $sample && (isset($sample['price']) || isset($sample['Price']) || isset($sample['cost']) || isset($sample['Cost'])),
                ]);
                        }
                        
                        // Parse the services array
                        if (is_array($data) && !empty($data)) {
                            $parsed = $this->parseServicesFromREST($data);
                            // Attempt to enrich prices using pricing endpoint if prices missing or zero
                            if (($parsed['success'] ?? false) && !empty($parsed['services'])) {
                                $parsed['services'] = $this->enrichServicesWithPricing($parsed['services']);
                            }
                            return $parsed;
                        }
                    }
                }
                
                // If REST API failed, return error
                Log::info('SMSPool getServices REST API failed');
                return [
                    'success' => false,
                    'error' => 'Services API not available',
                    'message' => 'Services list not available via API endpoint. Check logs for details.',
                ];
            });
        } catch (\Exception $e) {
            Log::error('SMSPool services fetch failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getPricing(array $params = []): array
    {
        return $this->fetchPricing($params);
    }

    /**
     * Fetch pricing from SMSPool pricing endpoint and enrich services.
     * Tries to map service ID -> price and apply multiplier/defaults.
     */
    private function enrichServicesWithPricing(array $services): array
    {
        try {
            $pricing = $this->fetchPricing();
            if (empty($pricing)) {
                return $services;
            }

            // Build a map of serviceId => min price (robust parsing per docs)
            $priceByServiceId = [];
            foreach ($pricing as $row) {
                if (!is_array($row)) continue;
                $sid = $row['service_id'] ?? $row['service'] ?? $row['id'] ?? null;
                $rawPrice = $row['price'] ?? $row['Price'] ?? $row['cost'] ?? $row['Cost'] ?? $row['amount'] ?? null;
                if ($sid === null || $rawPrice === null) continue;
                // Normalize price: allow strings like "0.05", "$0.05", "0,05"
                if (!is_numeric($rawPrice)) {
                    $normalized = preg_replace('/[^0-9.]/', '', (string)$rawPrice);
                    if ($normalized === '' || !is_numeric($normalized)) continue;
                    $rawPrice = $normalized;
                }
                $sid = (string)$sid;
                $val = (float)$rawPrice;
                if (!isset($priceByServiceId[$sid]) || $val < $priceByServiceId[$sid]) {
                    $priceByServiceId[$sid] = $val; // keep the cheapest seen
                }
            }

            $multiplier = (float) (Setting::get('sms_price_multiplier', 1.0));
            $defaultPrice = (float) (Setting::get('sms_default_service_price', 0));

            foreach ($services as &$svc) {
                $sid = isset($svc['id']) ? (string)$svc['id'] : null;
                $price = $svc['price'] ?? 0;
                if (($price === 0 || $price === null) && $sid && isset($priceByServiceId[$sid])) {
                    $price = (float) $priceByServiceId[$sid];
                }

                if ($price > 0) {
                    $price = round($price * ($multiplier > 0 ? $multiplier : 1), 3);
                } else {
                    $price = $defaultPrice > 0 ? round($defaultPrice, 3) : 0;
                }

                $svc['price'] = $price;
            }
            unset($svc);

            return $services;
        } catch (\Exception $e) {
            Log::warning('Failed to enrich services with pricing: ' . $e->getMessage());
            return $services;
        }
    }

    /**
     * Calls SMSPool pricing endpoint: POST https://api.smspool.net/request/pricing
     * Optional params can be extended later (country, service, pool, max_price).
     */
    private function fetchPricing(array $params = []): array
    {
        $url = 'https://api.smspool.net/request/pricing';
        $post = [
            'key' => $this->apiKey,
        ];

        // Defaults from settings
        $defaultPool = Setting::get('sms_pricing_pool'); // 0 = cheapest, 1 = highest success (per docs)
        $defaultCountry = Setting::get('sms_pricing_country'); // country id
        if (!isset($params['pool']) && $defaultPool !== null && $defaultPool !== '') {
            $post['pool'] = (string)$defaultPool;
        }
        if (!isset($params['country']) && $defaultCountry !== null && $defaultCountry !== '') {
            $post['country'] = (string)$defaultCountry;
        }
        // Allow optional filters if provided
        foreach (['country','service','pool','max_price'] as $k) {
            if (isset($params[$k]) && $params[$k] !== null && $params[$k] !== '') {
                $post[$k] = (string)$params[$k];
            }
        }

        // If not provided, try default max price from settings
        if (!isset($post['max_price'])) {
            $defaultMax = Setting::get('sms_default_max_price');
            if ($defaultMax !== null && $defaultMax !== '') {
                $post['max_price'] = (string) number_format((float)$defaultMax, 2, '.', '');
            }
        }

        // Make POST request via cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        Log::info('SMSPool pricing response', [
            'http_code' => $httpCode,
            'error' => $error,
            'preview' => is_string($response) ? substr($response, 0, 500) : null,
        ]);

        if ($error || $httpCode !== 200 || !$response) {
            return [];
        }

        $data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            // Common shapes: { data: [...] }, { prices: [...] }, direct array
            if (isset($data['data']) && is_array($data['data'])) {
                return $data['data'];
            }
            if (isset($data['prices']) && is_array($data['prices'])) {
                return $data['prices'];
            }
            // If data is associative with nested arrays per service id
            $flat = [];
            $assocKeys = array_keys($data);
            $allNumericKeys = count(array_filter($assocKeys, 'is_int')) === count($assocKeys);
            if (!$allNumericKeys) {
                foreach ($data as $key => $val) {
                    if (is_array($val)) {
                        // Try to attach service id into row if missing
                        foreach ($val as $row) {
                            if (is_array($row)) {
                                if (!isset($row['service']) && !isset($row['service_id'])) {
                                    $row['service_id'] = $key;
                                }
                                $flat[] = $row;
                            }
                        }
                    }
                }
                if (!empty($flat)) return $flat;
            }
            return $data;
        }

        return [];
    }
    
    private function parseServicesFromREST(array $data): array
    {
        $services = [];
        
        // Popular services to prioritize
        $popularServices = ['whatsapp', 'facebook', 'google', 'instagram', 'telegram', 'twitter', 'tiktok', 'snapchat', 'amazon', 'microsoft', 'apple', 'twitter', 'x'];
        
        foreach ($data as $key => $item) {
            if (is_array($item)) {
                $serviceName = $item['name'] ?? $item['service'] ?? $item['service_name'] ?? $key;
                $serviceId = $item['ID'] ?? $item['id'] ?? $item['service_id'] ?? $key;
                
                // Check if it's a popular service
                $isPopular = false;
                foreach ($popularServices as $popular) {
                    if (stripos($serviceName, $popular) !== false) {
                        $isPopular = true;
                        break;
                    }
                }
                
                // Extract price from various possible fields (check all possible formats)
                $price = 0;
                $priceFields = ['price', 'Price', 'PRICE', 'cost', 'Cost', 'COST', 'rate', 'Rate', 'RATE', 
                               'price_usd', 'priceUSD', 'PriceUSD', 'amount', 'Amount'];
                foreach ($priceFields as $field) {
                    if (isset($item[$field]) && is_numeric($item[$field])) {
                        $price = floatval($item[$field]);
                        break;
                    }
                }

                // Apply pricing rules: multiplier and default fallback if provider didn't send price
                $multiplier = floatval(Setting::get('sms_price_multiplier', 1.0));
                $defaultPrice = floatval(Setting::get('sms_default_service_price', 0));
                if ($price > 0) {
                    $price = round($price * ($multiplier > 0 ? $multiplier : 1), 3);
                } else {
                    $price = $defaultPrice > 0 ? round($defaultPrice, 3) : 0;
                }
                
                // Extract count
                $count = 0;
                $countFields = ['count', 'Count', 'COUNT', 'quantity', 'Quantity', 'available', 'Available', 
                               'stock', 'Stock', 'numbers', 'Numbers'];
                foreach ($countFields as $field) {
                    if (isset($item[$field]) && is_numeric($item[$field])) {
                        $count = intval($item[$field]);
                        break;
                    }
                }
                
                $services[] = [
                    'id' => $serviceId,
                    'name' => $serviceName,
                    'country' => $item['country'] ?? $item['country_code'] ?? $item['countryName'] ?? 'ALL',
                    'price' => $price,
                    'count' => $count,
                    'popular' => $isPopular,
                ];
            }
        }
        
        // Sort: popular services first, then alphabetically
        usort($services, function($a, $b) {
            if ($a['popular'] && !$b['popular']) return -1;
            if (!$a['popular'] && $b['popular']) return 1;
            return strcasecmp($a['name'], $b['name']);
        });
        
        return [
            'success' => true,
            'services' => $services,
            'provider' => $this->getName(),
        ];
    }
    
    public function getCountries(): array
    {
        try {
            $cacheKey = 'sms_provider_smspool_countries';
            return Cache::remember($cacheKey, 3600, function () {
                // Use SMSPool API: GET to /country/retrieve_all (correct endpoint)
                $restUrl = 'https://api.smspool.net/country/retrieve_all';
                
                Log::info("SMSPool trying GET API for countries", ['url' => $restUrl, 'method' => 'GET']);
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $restUrl . '?key=' . urlencode($this->apiKey));
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Accept: application/json',
                ]);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                Log::info("SMSPool countries response", ['http_code' => $httpCode, 'response' => substr($response, 0, 500)]);
                
                // Parse response if successful
                if ($httpCode === 200 && $response) {
                    $data = json_decode($response, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                        $countries = [];
                        foreach ($data as $country) {
                            if (is_array($country)) {
                                $countries[] = [
                                    'id' => $country['ID'] ?? $country['id'] ?? null,
                                    'name' => $country['name'] ?? $country['country'] ?? null,
                                    'code' => $country['code'] ?? $country['country_code'] ?? null,
                                ];
                            }
                        }
                        
                        if (!empty($countries)) {
                            Log::info("SMSPool countries fetched successfully", ['count' => count($countries)]);
                            return [
                                'success' => true,
                                'countries' => $countries,
                            ];
                        }
                    }
                    
                    Log::info("SMSPool countries response", [
                        'http_code' => $httpCode,
                        'response' => substr($response, 0, 500),
                    ]);
                }
                
                // If all endpoints failed, return a basic list of common countries
                Log::warning("SMSPool countries API failed, using fallback list");
                $fallbackCountries = [
                    ['id' => 0, 'name' => 'United States', 'code' => 'US'],
                    ['id' => 1, 'name' => 'United Kingdom', 'code' => 'GB'],
                    ['id' => 2, 'name' => 'Canada', 'code' => 'CA'],
                    ['id' => 3, 'name' => 'Australia', 'code' => 'AU'],
                    ['id' => 4, 'name' => 'Germany', 'code' => 'DE'],
                    ['id' => 5, 'name' => 'France', 'code' => 'FR'],
                    ['id' => 6, 'name' => 'India', 'code' => 'IN'],
                    ['id' => 7, 'name' => 'Brazil', 'code' => 'BR'],
                    ['id' => 8, 'name' => 'Mexico', 'code' => 'MX'],
                    ['id' => 9, 'name' => 'Philippines', 'code' => 'PH'],
                ];
                
                return [
                    'success' => true,
                    'countries' => $fallbackCountries,
                ];
            });
        } catch (\Exception $e) {
            Log::error('SMSPool countries fetch failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function requestNumber(string $service, string $country = null, array $options = []): array
    {
        try {
            // First, try the handler API with action parameter (like balance endpoint)
            Log::info('SMSPool requesting number', ['service' => $service, 'country' => $country]);
            
            // Try to get service name if service is an ID
            // The API might need service name instead of ID, or vice versa
            $serviceName = null;
            $serviceId = $service;
            
            // Check if we can get service details from cache
            try {
                $servicesData = $this->getServices();
                if (isset($servicesData['services']) && is_array($servicesData['services'])) {
                    foreach ($servicesData['services'] as $svc) {
                        if (isset($svc['id']) && (string)$svc['id'] === (string)$service) {
                            $serviceName = $svc['name'] ?? null;
                            break;
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Could not fetch service details: ' . $e->getMessage());
            }
            
            // Try handler API first with different action names
            $actions = ['getNumber', 'requestNumber', 'purchaseNumber', 'orderNumber', 'getNumbersStatus'];
            
            foreach ($actions as $action) {
                $params = [
                    'service' => $service,
                ];
                
                if ($country && $country !== 'null' && $country !== '' && $country !== '0') {
                    $params['country'] = $country;
                }
                
                $response = $this->makeRequest($action, $params, 'POST');
                
                if (isset($response['success']) && $response['success']) {
                    // Check if we got an order_id or number
                    if (isset($response['order_id']) || isset($response['ID']) || isset($response['number']) || isset($response['phone'])) {
                        return [
                            'success' => true,
                            'order_id' => $response['order_id'] ?? $response['ID'] ?? null,
                            'number' => $response['number'] ?? $response['phone'] ?? null,
                            'service' => $service,
                            'country' => $country,
                            'provider' => $this->getName(),
                            'expires_at' => $response['expires_at'] ?? $response['expires'] ?? null,
                        ];
                    }
                }
                
                // If we got a specific error (not just "BAD_ACTION"), log it
                if (isset($response['message']) && strpos($response['message'], 'BAD_ACTION') === false) {
                    Log::info('SMSPool handler API response', ['action' => $action, 'response' => $response]);
                }
            }
            
            // If handler API didn't work, try REST API endpoints
            // Correct endpoint based on SMSPool documentation: /purchase/sms
            $endpoints = [
                'https://api.smspool.net/purchase/sms',
                'https://api.smspool.net/purchase/number',
                'https://api.smspool.net/sms/purchase',
                'https://api.smspool.net/sms/order',
            ];
            
            // Try different parameter names - might need 'service_id' instead of 'service'
            // Also try with/without country, and different formats
            $postDataVariations = [];
            
            // According to SMSPool API documentation for /purchase/sms:
            // Required: key, service, max_price, quantity
            // Optional: country, pool, pricing_option, areacode, exclude, create_token, activation_type, carrier
            
            // Build base request with required parameters
            $baseRequest = [
                'key' => $this->apiKey,
                'service' => (string)$service,
                // Max price: prefer user-sent option, then setting, then sensible default
                'max_price' => (string) number_format((float)($options['max_price'] ?? Setting::get('sms_default_max_price', 10.00)), 2, '.', ''),
                'quantity' => '1', // Default to 1 number
                // pricing_option per docs: 1 = highest success, 0 = cheapest
                // Note: In SMSPool UI, 0=highest success, 1=cheapest. Default to cheapest (1) unless overridden.
                'pricing_option' => (string) ($options['pricing_option'] ?? Setting::get('sms_pricing_option', 1)),
                'activation_type' => 'SMS', // SMS, VOICE, or FLASH
            ];
            
            // Add country if provided (required for specific country)
            if ($country && $country !== 'null' && $country !== '' && $country !== '0' && $country !== null) {
                $baseRequest['country'] = (string)$country;
                Log::info('SMSPool: Including country in request', ['country' => $country]);
            } else {
                Log::info('SMSPool: No country provided or country is null', ['country' => $country]);
            }
            
            $postDataVariations[] = $baseRequest;
            
            // Also try with country as integer if provided
            if ($country && $country !== 'null' && $country !== '' && $country !== '0' && $country !== null && is_numeric($country)) {
                $baseRequest2 = array_merge($baseRequest, ['country' => intval($country)]);
                $postDataVariations[] = $baseRequest2;
            }
            
            // Use first variation by default, but we'll try others if first fails
            $postData = $postDataVariations[0];
            
            $response = null;
            $httpCode = 0;
            $error = null;
            $finalUrl = null;
            
            // Try each endpoint with each parameter variation
            foreach ($endpoints as $restUrl) {
                foreach ($postDataVariations as $variationIndex => $testPostData) {
                    // Try form data first
                    $requestData = http_build_query($testPostData);
                    $contentType = 'application/x-www-form-urlencoded';
                    
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $restUrl);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $requestData);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                    curl_setopt($ch, CURLOPT_VERBOSE, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Accept: application/json',
                        'Content-Type: ' . $contentType,
                        'User-Agent: BiggestLogs/1.0',
                    ]);
                    
                    $testResponse = curl_exec($ch);
                    $testHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $testError = curl_error($ch);
                    $testFinalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                    $testContentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                    curl_close($ch);
                    
                    // Log attempt but truncate long responses
                    Log::info('SMSPool request number attempt', [
                        'url' => $restUrl,
                        'variation' => $variationIndex + 1,
                        'final_url' => $testFinalUrl,
                        'http_code' => $testHttpCode,
                        'content_type' => $testContentType,
                        'response_preview' => substr($testResponse, 0, 1000),
                        'params' => array_merge($testPostData, ['key' => '***']),
                    ]);
                    
                    // If we get a 500 with empty response, log raw response
                    if ($testHttpCode === 500 && empty($testResponse)) {
                        Log::error('SMSPool 500 error - empty response', [
                            'url' => $restUrl,
                            'variation' => $variationIndex + 1,
                            'params' => $testPostData,
                        ]);
                    }
                    
                    // If we get a 200, use this response
                    if ($testHttpCode === 200) {
                        $response = $testResponse;
                        $httpCode = $testHttpCode;
                        $finalUrl = $testFinalUrl;
                        $postData = $testPostData; // Use the working variation
                        Log::info('SMSPool found working endpoint and params!', [
                            'url' => $restUrl, 
                            'variation' => $variationIndex + 1,
                            'response' => substr($response, 0, 300)
                        ]);
                        break 2; // Break out of both loops
                    } elseif ($testHttpCode === 400 || $testHttpCode === 422) {
                        // Client error - might have response body with error details
                        if (empty($response) || $httpCode === 500) {
                            $response = $testResponse;
                            $httpCode = $testHttpCode;
                            $error = $testError;
                            $finalUrl = $testFinalUrl;
                            $postData = $testPostData;
                        }
                        Log::warning('SMSPool client error', [
                            'url' => $restUrl,
                            'variation' => $variationIndex + 1,
                            'http_code' => $testHttpCode,
                            'response' => substr($testResponse, 0, 500),
                            'params' => array_merge($testPostData, ['key' => '***']),
                        ]);
                        if (strpos($restUrl, '/purchase/sms') !== false) {
                            break 2; // Use this response to see the error
                        }
                    } elseif ($testHttpCode !== 404 && !$response) {
                        // Store first non-404/400 response for debugging
                        $response = $testResponse;
                        $httpCode = $testHttpCode;
                        $error = $testError;
                        $finalUrl = $testFinalUrl;
                        $postData = $testPostData;
                        Log::warning('SMSPool API response', [
                            'url' => $restUrl,
                            'variation' => $variationIndex + 1,
                            'http_code' => $testHttpCode,
                            'response' => substr($response, 0, 500),
                            'params' => array_merge($testPostData, ['key' => '***']),
                        ]);
                    }
                }
            }
            
            // If all endpoints returned 404, log summary
            if ($httpCode === 0 || $httpCode === 404) {
                Log::error('SMSPool: All purchase endpoints returned 404. Please check API documentation for correct endpoint name.');
            }
            
            Log::info('SMSPool request number final response', [
                'final_url' => $finalUrl,
                'http_code' => $httpCode,
                'error' => $error,
                'response' => substr($response, 0, 500),
            ]);
            
            if ($response && ($httpCode === 200 || $httpCode === 500 || $httpCode === 400)) {
                // Try to parse as JSON first (even for error responses)
                $data = json_decode($response, true);
                
                if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                    // Check for success - SMSPool returns order_id and number on success
                    if (isset($data['order_id']) || isset($data['orderid']) || isset($data['ID']) || isset($data['id'])) {
                        $orderId = $data['order_id'] ?? $data['orderid'] ?? $data['ID'] ?? $data['id'] ?? null;
                        $phoneNumber = $data['number'] ?? $data['phone'] ?? $data['phone_number'] ?? null;
                        
                        return [
                            'success' => true,
                            'order_id' => $orderId,
                            'number' => $phoneNumber,
                            'service' => $service,
                            'country' => $country,
                            'provider' => $this->getName(),
                            'expires_at' => isset($data['expires_at']) ? $data['expires_at'] : (isset($data['expires']) ? $data['expires'] : null),
                        ];
                    }
                    
                    // Check for error messages in JSON response
                    if (isset($data['error']) || isset($data['message']) || isset($data['status']) || isset($data['msg'])) {
                        $errorMsg = $data['error'] ?? $data['message'] ?? $data['status'] ?? $data['msg'] ?? 'Failed to request number';
                        return [
                            'success' => false,
                            'error' => is_string($errorMsg) ? $errorMsg : json_encode($errorMsg),
                        ];
                    }
                    
                    // If success field exists but no order_id
                    if (isset($data['success']) && $data['success'] == 0) {
                        return [
                            'success' => false,
                            'error' => $data['message'] ?? $data['msg'] ?? 'Purchase failed',
                        ];
                    }
                } else {
                    // Response might be plain text
                    if (strpos($response, 'ACCESS_NUMBER') === 0 || strpos($response, 'ORDER_ID') === 0) {
                        $parts = explode(':', $response);
                        if (count($parts) >= 2) {
                            return [
                                'success' => true,
                                'order_id' => $parts[1] ?? null,
                                'number' => $parts[2] ?? null,
                                'service' => $service,
                                'country' => $country,
                                'provider' => $this->getName(),
                            ];
                        }
                    }
                    
                    if (strpos($response, 'ERROR') === 0 || strpos($response, 'BAD_') === 0 || strpos($response, 'NO_NUMBER') === 0) {
                        $errorMsg = strpos($response, ':') !== false 
                            ? substr($response, strpos($response, ':') + 1)
                            : $response;
                        return [
                            'success' => false,
                            'error' => trim($errorMsg),
                        ];
                    }
                }
            }
            
            // If we get here, the request failed
            $errorMessage = 'Failed to request number. HTTP ' . $httpCode;
            if ($response) {
                $errorMessage .= ': ' . substr($response, 0, 300);
            }
            if ($error) {
                $errorMessage .= ' (' . $error . ')';
            }
            
            return [
                'success' => false,
                'error' => $errorMessage,
            ];
        } catch (\Exception $e) {
            Log::error('SMSPool number request failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getMessages(string $orderId): array
    {
        try {
            $response = $this->makeRequest('getStatus', ['id' => $orderId]);
            
            if (isset($response['success']) && $response['success']) {
                $messages = [];
                
                if (isset($response['sms'])) {
                    $messages[] = [
                        'code' => $response['sms']['code'] ?? null,
                        'text' => $response['sms']['text'] ?? null,
                        'received_at' => $response['sms']['time'] ?? null,
                    ];
                }
                
                return [
                    'success' => true,
                    'status' => $response['status'] ?? 'pending',
                    'messages' => $messages,
                    'order_id' => $orderId,
                ];
            }
            
            return [
                'success' => false,
                'error' => $response['message'] ?? 'Failed to get messages',
                'status' => 'error',
            ];
        } catch (\Exception $e) {
            Log::error('SMSPool messages fetch failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 'error',
            ];
        }
    }

    private function makeRequest(string $action, array $params = [], string $method = 'GET'): array
    {
        // SMSPool uses a single endpoint with action parameter
        $url = $this->baseUrl;
        
        // Build params - api_key is always first, then action, then other params
        $requestParams = [
            'api_key' => $this->apiKey,
            'action' => $action,
        ];
        
        // Merge additional params
        $requestParams = array_merge($requestParams, $params);
        
        // Log the request for debugging
        Log::info("SMSPool API Request", [
            'action' => $action,
            'url' => $url,
            'params' => array_merge($requestParams, ['api_key' => '***']), // Hide API key in logs
        ]);
        
        try {
            // Use cURL as fallback if Guzzle is not available
            if (!class_exists('\GuzzleHttp\Client')) {
                return $this->makeCurlRequest($url, $requestParams, $method);
            }
            
            if ($method === 'POST') {
                $response = Http::asForm()->post($url, $requestParams);
            } else {
                $response = Http::get($url, $requestParams);
            }
            
            // Log response for debugging
            Log::info("SMSPool API Response [{$action}]: ", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            
            if ($response->successful()) {
                return $this->parseResponse($response->body(), $action);
            }
            
            return [
                'success' => false,
                'message' => 'API request failed: ' . $response->body(),
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error("SMSPool API request failed: {$action} - " . $e->getMessage());
            // Fallback to cURL on exception
            try {
                return $this->makeCurlRequest($url, $requestParams, $method);
            } catch (\Exception $curlError) {
                return [
                    'success' => false,
                    'message' => $e->getMessage() . ' | cURL: ' . $curlError->getMessage(),
                ];
            }
        }
    }

    private function parseResponse(string $responseBody, string $action): array
    {
        // SMSPool API returns different formats based on action
        $responseBody = trim($responseBody);
        
        // Try JSON first
        $jsonData = json_decode($responseBody, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
            if (isset($jsonData['success'])) {
                return $jsonData;
            }
            return array_merge(['success' => true], $jsonData);
        }
        
        // Handle text-based responses
        switch ($action) {
            case 'getBalance':
            case 'balance':
            case 'get_balance':
                // Balance: Returns "ACCESS_BALANCE:<amount>" or just the number
                if (is_numeric($responseBody)) {
                    return [
                        'success' => true,
                        'balance' => floatval($responseBody),
                        'currency' => 'USD',
                    ];
                }
                if (strpos($responseBody, 'ACCESS_BALANCE:') === 0) {
                    $balance = floatval(substr($responseBody, 15));
                    return [
                        'success' => true,
                        'balance' => $balance,
                        'currency' => 'USD',
                    ];
                }
                if (strpos($responseBody, 'ERROR:') === 0 || strpos($responseBody, 'BAD_') === 0) {
                    $errorMsg = strpos($responseBody, ':') !== false 
                        ? substr($responseBody, strpos($responseBody, ':') + 1)
                        : $responseBody;
                    return [
                        'success' => false,
                        'message' => trim($errorMsg),
                    ];
                }
                break;
                
            case 'getNumbersStatus':
            case 'services':
            case 'getNumbersStatusAndCost':
                // SMSPool returns services as JSON or text format
                // Try to parse as JSON first
                if (!empty($responseBody) && ($responseBody[0] === '{' || $responseBody[0] === '[')) {
                    $jsonData = json_decode($responseBody, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
                        return [
                            'success' => true,
                            'services' => $jsonData,
                            'raw_data' => $jsonData,
                        ];
                    }
                }
                // Handle text format (service_id:count:price format)
                if (!empty($responseBody) && strpos($responseBody, 'ERROR') !== 0 && strpos($responseBody, 'BAD_') !== 0) {
                    $lines = explode("\n", $responseBody);
                    $services = [];
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (empty($line)) continue;
                        
                        // Format: service_id:count:price or similar
                        $parts = explode(':', $line);
                        if (count($parts) >= 3) {
                            $services[] = [
                                'service' => $parts[0],
                                'id' => $parts[0],
                                'name' => $parts[0],
                                'count' => intval($parts[1]),
                                'price' => floatval($parts[2]),
                                'country' => 'ALL',
                            ];
                        }
                    }
                    if (!empty($services)) {
                        return [
                            'success' => true,
                            'services' => $services,
                        ];
                    }
                }
                break;
        }
        
        // Check for errors
        if (stripos($responseBody, 'ERROR') !== false || stripos($responseBody, 'BAD_') !== false) {
            return [
                'success' => false,
                'message' => $responseBody,
            ];
        }
        
        // Unknown format
        return [
            'success' => true,
            'raw_response' => $responseBody,
        ];
    }

    private function makeCurlRequest(string $url, array $params, string $method): array
    {
        $ch = curl_init();
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        } else {
            $queryString = http_build_query($params);
            curl_setopt($ch, CURLOPT_URL, $url . '?' . $queryString);
        }
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $action = $params['action'] ?? 'unknown';
        
        Log::info("SMSPool cURL Request", [
            'url' => $url,
            'method' => $method,
            'action' => $action,
            'http_code' => $httpCode,
            'error' => $error,
            'response' => substr($response, 0, 500),
        ]);
        
        curl_close($ch);
        
        if ($error) {
            Log::error("SMSPool cURL error: {$error}");
            return [
                'success' => false,
                'message' => "Connection error: {$error}",
            ];
        }
        
        if ($httpCode === 200 && $response) {
            return $this->parseResponse($response, $action);
        }
        
        return [
            'success' => false,
            'message' => "HTTP {$httpCode}: " . ($response ? substr($response, 0, 100) : 'No response'),
            'status' => $httpCode,
            'raw_response' => $response,
        ];
    }
}

