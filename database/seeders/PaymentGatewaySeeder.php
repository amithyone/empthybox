<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $gateways = [
            [
                'name' => 'Paystack',
                'code' => 'paystack',
                'display_name' => 'Paystack',
                'description' => 'Pay with Paystack - Secure payment processing',
                'icon' => '💳',
                'is_active' => true,
                'is_enabled' => !empty(env('PAYSTACK_PUBLIC_KEY')),
                'sort_order' => 1,
                'config' => [
                    'public_key' => env('PAYSTACK_PUBLIC_KEY'),
                    'secret_key' => env('PAYSTACK_SECRET_KEY'),
                    'merchant_email' => env('PAYSTACK_MERCHANT_EMAIL'),
                ],
                'supported_currencies' => ['NGN', 'USD', 'GHS', 'ZAR', 'KES'],
                'min_amount' => 100,
                'max_amount' => 10000000,
                'fee_percentage' => 0,
                'fee_fixed' => 0,
            ],
            [
                'name' => 'Stripe',
                'code' => 'stripe',
                'display_name' => 'Stripe',
                'description' => 'Pay with Stripe - Global payment processing',
                'icon' => '💳',
                'is_active' => true,
                'is_enabled' => !empty(env('STRIPE_KEY')),
                'sort_order' => 2,
                'config' => [
                    'public_key' => env('STRIPE_KEY'),
                    'secret_key' => env('STRIPE_SECRET'),
                ],
                'supported_currencies' => ['USD', 'EUR', 'GBP', 'NGN'],
                'min_amount' => 100,
                'max_amount' => 10000000,
                'fee_percentage' => 0,
                'fee_fixed' => 0,
            ],
            [
                'name' => 'Razorpay',
                'code' => 'razorpay',
                'display_name' => 'Razorpay',
                'description' => 'Pay with Razorpay - Indian payment gateway',
                'icon' => '💳',
                'is_active' => true,
                'is_enabled' => !empty(env('RAZORPAY_KEY')),
                'sort_order' => 3,
                'config' => [
                    'key_id' => env('RAZORPAY_KEY'),
                    'key_secret' => env('RAZORPAY_SECRET'),
                ],
                'supported_currencies' => ['INR', 'USD'],
                'min_amount' => 100,
                'max_amount' => 10000000,
                'fee_percentage' => 0,
                'fee_fixed' => 0,
            ],
            [
                'name' => 'PayVibe',
                'code' => 'payvibe',
                'display_name' => 'PayVibe',
                'description' => 'Pay with PayVibe - Secure payment processing',
                'icon' => '💳',
                'is_active' => true,
                'is_enabled' => !empty(env('PAYVIBE_API_KEY')),
                'sort_order' => 4,
                'config' => [
                    'base_url' => env('PAYVIBE_BASE_URL', 'https://payvibeapi.six3tech.com/api/v1'),
                    'public_key' => env('PAYVIBE_API_KEY'),
                    'secret_key' => env('PAYVIBE_SECRET_KEY'),
                    'product_identifier' => env('PAYVIBE_PRODUCT_IDENTIFIER', 'biggestlogs'),
                ],
                'supported_currencies' => ['NGN', 'USD'],
                'min_amount' => 100,
                'max_amount' => 10000000,
                'fee_percentage' => 0,
                'fee_fixed' => 0,
            ],
            [
                'name' => 'BTCPay Server',
                'code' => 'btcpay',
                'display_name' => 'Bitcoin (BTCPay)',
                'description' => 'Pay with Bitcoin via BTCPay Server',
                'icon' => '₿',
                'is_active' => true,
                'is_enabled' => false, // Set to true when configured
                'sort_order' => 5,
                'config' => [
                    'server_url' => env('BTCPAY_SERVER_URL'),
                    'api_key' => env('BTCPAY_API_KEY'),
                    'store_id' => env('BTCPAY_STORE_ID'),
                ],
                'supported_currencies' => ['BTC', 'USD'],
                'min_amount' => 0.0001,
                'max_amount' => 10000000,
                'fee_percentage' => 0,
                'fee_fixed' => 0,
            ],
            [
                'name' => 'CoinGate',
                'code' => 'coingate',
                'display_name' => 'Cryptocurrency (CoinGate)',
                'description' => 'Pay with various cryptocurrencies via CoinGate',
                'icon' => '₿',
                'is_active' => true,
                'is_enabled' => false, // Set to true when configured
                'sort_order' => 6,
                'config' => [
                    'api_key' => env('COINGATE_API_KEY'),
                    'environment' => env('COINGATE_ENVIRONMENT', 'sandbox'),
                ],
                'supported_currencies' => ['BTC', 'ETH', 'LTC', 'USD'],
                'min_amount' => 0.0001,
                'max_amount' => 10000000,
                'fee_percentage' => 0,
                'fee_fixed' => 0,
            ],
            [
                'name' => 'Manual Payment',
                'code' => 'manual',
                'display_name' => 'Bank Transfer',
                'description' => 'Manual payment via bank transfer - Requires admin approval',
                'icon' => '🏦',
                'is_active' => true,
                'is_enabled' => true,
                'sort_order' => 7,
                'config' => null,
                'supported_currencies' => ['NGN', 'USD'],
                'min_amount' => 100,
                'max_amount' => 10000000,
                'fee_percentage' => 0,
                'fee_fixed' => 0,
                'instructions' => 'Make a bank transfer to the provided account details and upload your payment receipt for admin approval.',
            ],
        ];

        foreach ($gateways as $gateway) {
            PaymentGateway::updateOrCreate(
                ['code' => $gateway['code']],
                $gateway
            );
        }
    }
}
