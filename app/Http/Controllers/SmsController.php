<?php

namespace App\Http\Controllers;

use App\Models\SmsOrder;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class SmsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Check if SMS is marked as coming soon
        $smsComingSoon = Setting::get('sms_coming_soon', false);
        
        // If coming soon and user is not admin, show coming soon page
        if ($smsComingSoon && (!auth()->check() || !auth()->user()->is_admin)) {
            return view('sms.coming-soon');
        }
        
        $smsService = new SmsService();
        $services = $smsService->getServices();
        $balance = $smsService->getBalance();
        $countries = $smsService->getCountries();
        
        return view('sms.index', compact('services', 'balance', 'countries'));
    }

    public function chooseProvider()
    {
        // Check if SMS is marked as coming soon
        $smsComingSoon = Setting::get('sms_coming_soon', false);
        
        // If coming soon and user is not admin, show coming soon page
        if ($smsComingSoon && (!auth()->check() || !auth()->user()->is_admin)) {
            return view('sms.coming-soon');
        }
        
        $activeProvider = Setting::get('sms_active_provider', 'smspool');
        return view('sms.select', compact('activeProvider'));
    }

    public function indexProvider(string $provider)
    {
        // Check if SMS is marked as coming soon
        $smsComingSoon = Setting::get('sms_coming_soon', false);
        
        // If coming soon and user is not admin, show coming soon page
        if ($smsComingSoon && (!auth()->check() || !auth()->user()->is_admin)) {
            return view('sms.coming-soon');
        }
        
        $provider = strtolower($provider);
        if (!in_array($provider, ['smspool', 'tigersms', 'all'])) {
            abort(404);
        }
        // Switch active provider for this session/app
        Setting::set('sms_active_provider', $provider);
        // Clear only the selected provider cache so data loads fresh
        $smsService = new SmsService();
        $smsService->clearCache($provider === 'all' ? null : $provider);
        $services = $smsService->getServices();
        $balance = $smsService->getBalance();
        $countries = $smsService->getCountries();
        
        return view('sms.index', compact('services', 'balance', 'countries'));
    }

    public function inbox()
    {
        // Check if SMS is marked as coming soon
        $smsComingSoon = Setting::get('sms_coming_soon', false);
        
        // If coming soon and user is not admin, show coming soon page
        if ($smsComingSoon && (!auth()->check() || !auth()->user()->is_admin)) {
            return view('sms.coming-soon');
        }
        
        $orders = auth()->user()->smsOrders()
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('sms.inbox', compact('orders'));
    }

    public function requestNumber(Request $request)
    {
        // Check if SMS is marked as coming soon
        $smsComingSoon = Setting::get('sms_coming_soon', false);
        
        // If coming soon and user is not admin, return error
        if ($smsComingSoon && (!auth()->check() || !auth()->user()->is_admin)) {
            return response()->json([
                'success' => false,
                'error' => 'SMS service is coming soon. Check back later!',
            ], 403);
        }
        
        $request->validate([
            'service' => 'required|string',
            'country' => 'nullable|string',
            'max_price' => 'nullable|numeric|min:0',
        ]);

        $smsService = new SmsService();
        $result = $smsService->requestNumber(
            $request->service,
            $request->country,
            [
                'max_price' => $request->max_price,
            ]
        );

        // If successful, save to database
        if (isset($result['success']) && $result['success']) {
            // Get service name from services list
            $services = $smsService->getServices();
            $serviceName = null;
            if (isset($services['services']) && is_array($services['services'])) {
                foreach ($services['services'] as $svc) {
                    if (isset($svc['id']) && $svc['id'] == $request->service) {
                        $serviceName = $svc['name'] ?? $svc['service'] ?? null;
                        break;
                    }
                }
            }

            // Get country name
            $countries = $smsService->getCountries();
            $countryName = null;
            if (isset($countries['countries']) && is_array($countries['countries'])) {
                foreach ($countries['countries'] as $ctr) {
                    if (isset($ctr['id']) && $ctr['id'] == $request->country) {
                        $countryName = $ctr['name'] ?? null;
                        break;
                    }
                }
            }

            SmsOrder::create([
                'user_id' => auth()->id(),
                'provider_order_id' => $result['order_id'] ?? null,
                'service_id' => $request->service,
                'service_name' => $serviceName,
                'country_id' => $request->country,
                'country_name' => $countryName,
                'phone_number' => $result['number'] ?? null,
                'status' => $result['number'] ? 'active' : 'pending',
                'expires_at' => isset($result['expires_at']) ? $result['expires_at'] : now()->addHours(24),
            ]);

            $result['message'] = 'Number requested successfully! Check your inbox for updates.';
        }

        return response()->json($result);
    }

    public function checkStatus(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string',
        ]);

        $smsService = new SmsService();
        $result = $smsService->getMessages($request->order_id);

        // Update database if SMS received
        if (isset($result['success']) && $result['success'] && isset($result['sms_code'])) {
            $smsOrder = SmsOrder::where('provider_order_id', $request->order_id)
                ->orWhere('id', $request->order_id)
                ->first();
            
            if ($smsOrder && $smsOrder->user_id === auth()->id()) {
                $smsOrder->update([
                    'status' => 'completed',
                    'sms_code' => $result['sms_code'],
                    'sms_text' => $result['sms_text'] ?? null,
                    'sms_received_at' => now(),
                ]);
            }
        }

        return response()->json($result);
    }

    public function getServiceCountries(Request $request)
    {
        $request->validate([
            'service' => 'required|string',
        ]);

        $smsService = new SmsService();
        $provider = $smsService->getActiveProvider();
        
        if (!$provider) {
            return response()->json([
                'success' => false,
                'error' => 'No SMS provider configured',
            ]);
        }

        // Check if provider supports getting countries for a service
        if (method_exists($provider, 'getAvailableCountriesForService')) {
            $result = $provider->getAvailableCountriesForService($request->service);
            return response()->json($result);
        }

        // Fallback: return all countries
        $countries = $smsService->getCountries();
        return response()->json($countries);
    }

    public function pricing(Request $request)
    {
        $request->validate([
            'country' => 'nullable|string',
            'service' => 'nullable|string',
            'pool' => 'nullable|string',
            'max_price' => 'nullable|numeric',
        ]);

        $smsService = new SmsService();
        $params = [
            'country' => $request->country,
            'service' => $request->service,
            'pool' => $request->pool,
            'max_price' => $request->max_price,
        ];
        $pricing = $smsService->getPricing($params);

        return response()->json($pricing);
    }

    public function testTigerServiceCountries(Request $request)
    {
        // Force TigerSMS for this test
        Setting::set('sms_active_provider', 'tigersms');
        $serviceId = (string)($request->query('service', '22'));

        $smsService = new SmsService();
        $provider = $smsService->getActiveProvider();
        if (!$provider || !method_exists($provider, 'getAvailableCountriesForService')) {
            return response()->json(['success' => false, 'error' => 'Provider not available or feature unsupported']);
        }

        $result = $provider->getAvailableCountriesForService($serviceId);
        return response()->json($result);
    }

    public function testTigerPurchase(Request $request)
    {
        $this->middleware('auth');
        // Force TigerSMS as active provider for this test
        Setting::set('sms_active_provider', 'tigersms');
        $smsService = new SmsService();
        $provider = $smsService->getActiveProvider();
        
        if (!$provider || !($provider instanceof \App\Services\Providers\TigerSmsProvider)) {
            return response()->json([
                'success' => false,
                'error' => 'TigerSMS provider not available'
            ]);
        }

        // Get all services
        $services = $smsService->getServices();
        $allServices = $services['services'] ?? [];
        
        // Filter to get first 10 services with available countries
        $testServices = [];
        foreach (array_slice($allServices, 0, 20) as $service) {
            $serviceId = $service['id'] ?? null;
            if (!$serviceId) continue;
            
            // Get available countries for this service
            $availableCountries = $provider->getAvailableCountriesForService($serviceId);
            $countries = $availableCountries['countries'] ?? [];
            
            if (!empty($countries)) {
                $testServices[] = [
                    'service' => $service,
                    'countries' => array_slice($countries, 0, 3), // Take first 3 countries
                ];
            }
            
            // Limit to 10 services for testing
            if (count($testServices) >= 10) break;
        }
        
        // Test purchases
        $results = [];
        foreach ($testServices as $testData) {
            $serviceId = $testData['service']['id'];
            $serviceName = $testData['service']['name'];
            
            // Try with first available country
            $firstCountry = $testData['countries'][0] ?? null;
            if (!$firstCountry) continue;
            
            $countryId = $firstCountry['id'];
            $countryName = $firstCountry['name'];
            
            try {
                Log::info('TigerSMS test purchase', [
                    'service' => $serviceId,
                    'service_name' => $serviceName,
                    'country' => $countryId,
                    'country_name' => $countryName,
                ]);
                
                $result = $provider->requestNumber(
                    $serviceId,
                    $countryId,
                    []
                );
                
                $results[] = [
                    'service_id' => $serviceId,
                    'service_name' => $serviceName,
                    'country_id' => $countryId,
                    'country_name' => $countryName,
                    'success' => $result['success'] ?? false,
                    'error' => $result['error'] ?? null,
                    'order_id' => $result['order_id'] ?? null,
                    'number' => $result['number'] ?? null,
                ];
                
                // If successful, stop testing this service
                if ($result['success'] ?? false) {
                    break; // Move to next service
                }
            } catch (\Exception $e) {
                $results[] = [
                    'service_id' => $serviceId,
                    'service_name' => $serviceName,
                    'country_id' => $countryId,
                    'country_name' => $countryName,
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }
        
        // Count successes
        $successCount = count(array_filter($results, fn($r) => $r['success']));
        $totalCount = count($results);
        
        return response()->json([
            'success' => true,
            'summary' => [
                'total_services_tested' => $totalCount,
                'successful_purchases' => $successCount,
                'failed_purchases' => $totalCount - $successCount,
            ],
            'results' => $results,
            'recommendation' => $successCount > 0 
                ? "✓ {$successCount} out of {$totalCount} purchases succeeded! TigerSMS is working."
                : "✗ All purchases failed. Check logs for details.",
        ]);
    }
    
    private function getRecommendation(array $results): string
    {
        foreach ($results as $result) {
            if ($result['success']) {
                return "✓ Working: {$result['test']} with service='{$result['params']['service']}' and country='{$result['params']['country']}'";
            }
        }
        return "✗ None of the parameter combinations worked. Check logs for details.";
    }

    public function testTiger(Request $request)
    {
        $this->middleware('auth');
        // Force TigerSMS as active provider for this test
        Setting::set('sms_active_provider', 'tigersms');
        $smsService = new SmsService();
        // Clear only Tiger cache
        $smsService->clearCache('tigersms');

        $params = [
            'service' => $request->query('service'),
            'country' => $request->query('country'),
        ];

        $data = $smsService->getPricing(array_filter($params));
        Log::info('TigerSMS manual test endpoint', [
            'params' => array_filter($params),
            'success' => $data['success'] ?? null,
            'keys' => isset($data['data']) && is_array($data['data']) ? array_slice(array_keys($data['data']), 0, 10) : [],
        ]);

        return response()->json([
            'success' => true,
            'provider' => 'tigersms',
            'params' => array_filter($params),
            'count' => isset($data['data']) && is_array($data['data']) ? count($data['data']) : 0,
            'sample' => isset($data['data']) && is_array($data['data']) ? array_slice($data['data'], 0, 1, true) : [],
        ]);
    }
    
    public function testTigerServices(Request $request)
    {
        $this->middleware('auth');
        Setting::set('sms_active_provider', 'tigersms');
        $smsService = new SmsService();
        $smsService->clearCache('tigersms');
        
        $services = $smsService->getServices();
        $countries = $smsService->getCountries();
        
        // Log sample data for debugging
        Log::info('TigerSMS test endpoint', [
            'services_sample' => isset($services['services']) && is_array($services['services']) ? array_slice($services['services'], 0, 3) : null,
            'countries_sample' => isset($countries['countries']) && is_array($countries['countries']) ? array_slice($countries['countries'], 0, 3) : null,
        ]);
        
        return response()->json([
            'services' => $services,
            'countries' => $countries,
            'services_count' => isset($services['services']) ? count($services['services']) : 0,
            'countries_count' => isset($countries['countries']) ? count($countries['countries']) : 0,
            'first_service' => isset($services['services'][0]) ? $services['services'][0] : null,
            'first_country' => isset($countries['countries'][0]) ? $countries['countries'][0] : null,
        ]);
    }
}
