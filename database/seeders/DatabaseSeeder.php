<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCredential;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Seed payment gateways
        $this->call(PaymentGatewaySeeder::class);

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@biggestlogs.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );
        // Always ensure admin flag is set
        $admin->update(['is_admin' => true]);
        if (!$admin->wallet) {
            Wallet::create(['user_id' => $admin->id]);
        }

        // Create test user
        $user = User::firstOrCreate(
            ['email' => 'user@test.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
            ]
        );
        if (!$user->wallet) {
            Wallet::create(['user_id' => $user->id, 'balance' => 100]);
        }

        // Create categories
        $instagram = Category::firstOrCreate(
            ['slug' => 'instagram-logs'],
            [
                'name' => 'BiggestLogs 🔥 Instagram Logs',
                'description' => 'Premium Instagram accounts',
                'icon' => '📸',
                'sort_order' => 1,
            ]
        );

        $tiktok = Category::firstOrCreate(
            ['slug' => 'tiktok-logs'],
            [
                'name' => 'BiggestLogs 🔥 TikTok Logs',
                'description' => 'Verified TikTok accounts',
                'icon' => '🎵',
                'sort_order' => 2,
            ]
        );

        $snapchat = Category::firstOrCreate(
            ['slug' => 'snapchat-logs'],
            [
                'name' => 'BiggestLogs 🔥 Snapchat Logs',
                'description' => 'Premium Snapchat accounts',
                'icon' => '👻',
                'sort_order' => 3,
            ]
        );

        $twitter = Category::firstOrCreate(
            ['slug' => 'twitter-logs'],
            [
                'name' => 'BiggestLogs 🔥 Twitter/X Logs',
                'description' => 'Verified Twitter/X accounts',
                'icon' => '🐦',
                'sort_order' => 4,
            ]
        );

        // Create Instagram products
        $product1 = Product::firstOrCreate(
            ['slug' => 'instagram-verified-usa'],
            [
            'category_id' => $instagram->id,
            'name' => 'Instagram Verified Account - USA',
            'slug' => 'instagram-verified-usa',
            'description' => 'Premium Instagram account with blue verification badge. High follower count and engagement. Perfect for influencers and businesses.',
            'login_steps' => "1. Open Instagram app\n2. Click 'Forgot Password'\n3. Enter the email provided\n4. Follow password reset instructions",
            'access_tips' => 'Enable 2FA immediately after accessing. Change password within 24 hours for security.',
            'price' => 49.99,
            'account_type' => 'Verified Business',
            'region' => 'United States',
            'is_verified' => true,
            ]
        );

        $product2 = Product::firstOrCreate(
            ['slug' => 'instagram-creator-10k'],
            [
                'category_id' => $instagram->id,
                'name' => 'Instagram Creator Account - 10K+ Followers',
                'slug' => 'instagram-creator-10k',
                'description' => 'Instagram creator account with 10,000+ active followers. High engagement rate and monetization ready.',
                'login_steps' => "1. Open Instagram app\n2. Log in with provided credentials\n3. Verify your identity",
                'price' => 79.99,
                'account_type' => 'Creator',
                'region' => 'Global',
                'is_verified' => false,
            ]
        );

        $product3 = Product::firstOrCreate(
            ['slug' => 'instagram-brand-eu'],
            [
                'category_id' => $instagram->id,
                'name' => 'Instagram Brand Account - EU',
                'slug' => 'instagram-brand-eu',
                'description' => 'Professional brand account from Europe. Business verified with insights access. Great for marketing campaigns.',
                'login_steps' => "1. Access Instagram via browser\n2. Use provided email to login\n3. Complete security verification",
                'price' => 89.99,
                'account_type' => 'Business',
                'region' => 'Europe',
                'is_verified' => true,
            ]
        );

        // Create TikTok products
        $product4 = Product::firstOrCreate(
            ['slug' => 'tiktok-creator-account'],
            [
                'category_id' => $tiktok->id,
                'name' => 'TikTok Creator Account',
                'slug' => 'tiktok-creator-account',
                'description' => 'TikTok account with monetization enabled. Ready for content creation and live streaming.',
                'login_steps' => "1. Download TikTok app\n2. Log in with provided credentials\n3. Verify email if prompted",
                'price' => 39.99,
                'account_type' => 'Creator',
                'region' => 'Global',
            ]
        );

        $product5 = Product::create([
            'category_id' => $tiktok->id,
            'name' => 'TikTok Viral Account - 50K+ Followers',
            'slug' => 'tiktok-viral-50k',
            'description' => 'TikTok account with 50,000+ followers and viral content history. Perfect for immediate monetization.',
            'login_steps' => "1. Open TikTok app\n2. Login with credentials\n3. Enable creator tools",
            'price' => 149.99,
            'account_type' => 'Creator Plus',
            'region' => 'USA',
            'is_verified' => false,
        ]);

        $product6 = Product::create([
            'category_id' => $tiktok->id,
            'name' => 'TikTok Business Account',
            'slug' => 'tiktok-business-account',
            'description' => 'TikTok for Business account with ad credits and analytics access. Ready for marketing campaigns.',
            'login_steps' => "1. Access TikTok Ads Manager\n2. Login with provided credentials\n3. Set up payment method",
            'price' => 99.99,
            'account_type' => 'Business',
            'region' => 'Global',
            'is_verified' => true,
        ]);

        // Create Snapchat products
        $product7 = Product::create([
            'category_id' => $snapchat->id,
            'name' => 'Snapchat Premium Account',
            'slug' => 'snapchat-premium',
            'description' => 'Snapchat account with Snapchat+ subscription and premium features. High snap score included.',
            'login_steps' => "1. Open Snapchat app\n2. Login with credentials\n3. Verify phone number",
            'price' => 34.99,
            'account_type' => 'Premium',
            'region' => 'Global',
        ]);

        $product8 = Product::create([
            'category_id' => $snapchat->id,
            'name' => 'Snapchat Influencer Account',
            'slug' => 'snapchat-influencer',
            'description' => 'Snapchat account with established audience and high engagement. Perfect for brand partnerships.',
            'login_steps' => "1. Download Snapchat\n2. Login with provided info\n3. Complete setup",
            'price' => 59.99,
            'account_type' => 'Influencer',
            'region' => 'USA',
        ]);

        // Create Twitter/X products
        $product9 = Product::create([
            'category_id' => $twitter->id,
            'name' => 'Twitter Verified Blue Account',
            'slug' => 'twitter-verified-blue',
            'description' => 'Twitter/X account with Blue verification badge. High follower count and engagement metrics.',
            'login_steps' => "1. Go to X.com\n2. Login with credentials\n3. Verify account",
            'price' => 69.99,
            'account_type' => 'Verified',
            'region' => 'Global',
            'is_verified' => true,
        ]);

        $product10 = Product::create([
            'category_id' => $twitter->id,
            'name' => 'Twitter Creator Account',
            'slug' => 'twitter-creator',
            'description' => 'Twitter account optimized for creators with monetization features. Great for content creators.',
            'login_steps' => "1. Access X platform\n2. Use provided login\n3. Enable creator features",
            'price' => 44.99,
            'account_type' => 'Creator',
            'region' => 'Global',
        ]);

        $product11 = Product::create([
            'category_id' => $twitter->id,
            'name' => 'Twitter Business Account',
            'slug' => 'twitter-business',
            'description' => 'Professional Twitter business account with ads access. Perfect for marketing and promotions.',
            'login_steps' => "1. Login to X\n2. Access Twitter Ads\n3. Configure business settings",
            'price' => 84.99,
            'account_type' => 'Business',
            'region' => 'Global',
            'is_verified' => true,
        ]);

        $product12 = Product::create([
            'category_id' => $instagram->id,
            'name' => 'Instagram Influencer Account - 100K+',
            'slug' => 'instagram-influencer-100k',
            'description' => 'Massive Instagram account with 100,000+ followers. Premium influencer account ready for brand deals.',
            'login_steps' => "1. Open Instagram\n2. Login with credentials\n3. Secure account",
            'price' => 299.99,
            'account_type' => 'Influencer',
            'region' => 'Global',
            'is_verified' => true,
        ]);

        // Create sample credentials for all products
        $products = [
            ['product' => $product1, 'prefix' => 'ig'],
            ['product' => $product2, 'prefix' => 'ig'],
            ['product' => $product3, 'prefix' => 'ig'],
            ['product' => $product4, 'prefix' => 'tiktok'],
            ['product' => $product5, 'prefix' => 'tiktok'],
            ['product' => $product6, 'prefix' => 'tiktok'],
            ['product' => $product7, 'prefix' => 'snapchat'],
            ['product' => $product8, 'prefix' => 'snapchat'],
            ['product' => $product9, 'prefix' => 'twitter'],
            ['product' => $product10, 'prefix' => 'twitter'],
            ['product' => $product11, 'prefix' => 'twitter'],
            ['product' => $product12, 'prefix' => 'ig'],
        ];
        
        foreach ($products as $index => $item) {
            $product = $item['product'];
            $prefix = $item['prefix'];
            $count = $index < 4 ? 5 : 3; // First 4 products get 5 credentials, rest get 3
            for ($i = 1; $i <= $count; $i++) {
                ProductCredential::create([
                    'product_id' => $product->id,
                    'username' => $prefix . '_user_' . ($index + 1) . '_' . $i,
                    'password' => 'SecurePass' . ($index + 1) . $i . '!',
                    'email' => $prefix . ($index + 1) . '_' . $i . '@example.com',
                ]);
            }
        }
    }
}


