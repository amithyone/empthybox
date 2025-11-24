<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayVibeService
{
    protected $baseUrl;
    protected $publicKey;
    protected $secretKey;
    protected $productIdentifier;

    public function __construct()
    {
        $this->baseUrl = config('services.payvibe.base_url', env('PAYVIBE_BASE_URL', 'https://payvibeapi.six3tech.com/api/v1'));
        $this->publicKey = config('services.payvibe.public_key', env('PAYVIBE_API_KEY'));
        $this->secretKey = config('services.payvibe.secret_key', env('PAYVIBE_SECRET_KEY'));
        $this->productIdentifier = config('services.payvibe.product_identifier', env('PAYVIBE_PRODUCT_IDENTIFIER', 'biggestlogs'));
    }

    /**
     * Generate virtual account for payment
     */
    public function generateVirtualAccount($amount, $reference)
    {
        try {
            // Calculate charges: Fixed ₦100 + 1.5%
            $fixedCharge = 100;
            $percentageRate = 0.015;
            $percentageCharge = round($amount * $percentageRate, 2);
            $totalCharges = $fixedCharge + $percentageCharge;
            $finalAmount = round($amount + $totalCharges, 0);

            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ];

            if (!empty($this->secretKey)) {
                $headers['Authorization'] = 'Bearer ' . $this->secretKey;
            }

            $requestData = [
                'reference' => $reference,
                // Do not send amount when requesting virtual account number
                'service' => 'sms',
            ];

            if (!empty($this->productIdentifier)) {
                $requestData['product_identifier'] = $this->productIdentifier;
            }

            Log::info('PayVibe: Generating virtual account', [
                'reference' => $reference,
                'amount' => $amount,
                'final_amount' => $finalAmount,
                'charges' => $totalCharges,
                'api_key_set' => !empty($this->secretKey),
                'base_url' => $this->baseUrl
            ]);

            $response = Http::withHeaders($headers)
                ->withoutVerifying()
                ->post("{$this->baseUrl}/payments/virtual-accounts/initiate", $requestData);

            Log::info('PayVibe: API Response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if ($response->successful()) {
                $responseData = $response->json();

                if ($responseData['status'] == 'success' || $responseData['status'] === true) {
                    $virtualAccount = $responseData['data']['virtual_account_number'] ?? 
                                    $responseData['data']['virtual_account'] ?? 
                                    $responseData['virtual_account_number'] ?? 
                                    $responseData['virtual_account'] ?? 'N/A';

                    $bankName = $responseData['data']['bank_name'] ?? 
                               $responseData['bank_name'] ?? 'N/A';

                    $accountName = $responseData['data']['account_name'] ?? 
                                  $responseData['account_name'] ?? 'N/A';

                    return [
                        'success' => true,
                        'virtual_account' => $virtualAccount,
                        'bank_name' => $bankName,
                        'account_name' => $accountName,
                        'amount' => $finalAmount,
                        'reference' => $reference,
                        'charges' => $totalCharges,
                        'original_amount' => $amount,
                    ];
                }
            }

            Log::error('PayVibe: Failed to generate account', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to generate virtual account',
                'debug' => $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('PayVibe: Exception generating account', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify payment status
     */
    public function verifyPayment($reference)
    {
        try {
            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ];

            if (!empty($this->secretKey)) {
                $headers['Authorization'] = 'Bearer ' . $this->secretKey;
            }

            $response = Http::withHeaders($headers)
                ->withoutVerifying()
                ->post("{$this->baseUrl}/payments/virtual-accounts/verify", [
                    'reference' => $reference
                ]);

            Log::info('PayVibe: Verify payment response', [
                'reference' => $reference,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data;
            }

            return null;

        } catch (\Exception $e) {
            Log::error('PayVibe: Exception verifying payment', [
                'reference' => $reference,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Calculate charges for an amount
     */
    public function calculateCharges($amount)
    {
        $fixedCharge = 100;
        $percentageRate = 0.015;
        $percentageCharge = round($amount * $percentageRate, 2);
        $totalCharges = $fixedCharge + $percentageCharge;
        $finalAmount = round($amount + $totalCharges, 0);

        return [
            'original_amount' => $amount,
            'fixed_charge' => $fixedCharge,
            'percentage_rate' => $percentageRate,
            'percentage_charge' => $percentageCharge,
            'total_charges' => $totalCharges,
            'final_amount' => $finalAmount
        ];
    }
}

