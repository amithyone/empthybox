<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\PayVibeService;

class TestPayVibeController extends Controller
{
    public function test()
    {
        $service = new PayVibeService();
        
        $reflection = new \ReflectionClass($service);
        $secretKeyProp = $reflection->getProperty('secretKey');
        $secretKeyProp->setAccessible(true);
        $secretKey = $secretKeyProp->getValue($service);
        
        $baseUrlProp = $reflection->getProperty('baseUrl');
        $baseUrlProp->setAccessible(true);
        $baseUrl = $baseUrlProp->getValue($service);
        
        $productIdentifierProp = $reflection->getProperty('productIdentifier');
        $productIdentifierProp->setAccessible(true);
        $productIdentifier = $productIdentifierProp->getValue($service);
        
        return response()->json([
            'env' => [
                'PAYVIBE_BASE_URL' => env('PAYVIBE_BASE_URL'),
                'PAYVIBE_API_KEY' => env('PAYVIBE_API_KEY') ? substr(env('PAYVIBE_API_KEY'), 0, 20) . '...' : null,
                'PAYVIBE_SECRET_KEY' => env('PAYVIBE_SECRET_KEY') ? substr(env('PAYVIBE_SECRET_KEY'), 0, 20) . '...' : null,
                'PAYVIBE_PRODUCT_IDENTIFIER' => env('PAYVIBE_PRODUCT_IDENTIFIER'),
            ],
            'service' => [
                'base_url' => $baseUrl,
                'secret_key_set' => !empty($secretKey),
                'product_identifier' => $productIdentifier,
            ]
        ]);
    }
}

