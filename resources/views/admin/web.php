<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PayVibeController;
use App\Http\Controllers\PayVibeWebhookController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TestPayVibeController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

// Authentication (outside maintenance)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Webhooks (no auth required)
Route::post('/webhook/payvibe', [PayVibeWebhookController::class, 'handle'])->name('webhook.payvibe');

// Test route
Route::get('/test-payvibe-config', [TestPayVibeController::class, 'test'])->name('test.payvibe');

// Public routes (with maintenance check)
Route::middleware('maintenance')->group(function() {
    Route::get('/', function() {
        $categories = \App\Models\Category::where('is_active', true)->orderBy('sort_order')->get();
        $products = \App\Models\Product::with('category')->where('is_active', true)->latest()->paginate(12);
        return view('home', compact('categories', 'products'));
    })->name('home');

    // Products
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');
});

// Protected routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Profile
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    
    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/products/{product}/order', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/reveal', [OrderController::class, 'revealCredentials'])->name('orders.reveal');
    Route::post('/orders/{order}/replacement', [OrderController::class, 'requestReplacement'])->name('orders.replacement');
    
    // Wallet
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit');
    Route::get('/wallet/payvibe/{transaction}', [WalletController::class, 'showPayVibePayment'])->name('payvibe.payment');
    
    // PayVibe
    Route::post('/payvibe/generate-account', [PayVibeController::class, 'generateAccount'])->name('payvibe.generate');
    Route::post('/payvibe/check-payment-status', [PayVibeController::class, 'checkStatus'])->name('payvibe.check-status');
    
    // SMS Service
    Route::get('/sms', [\App\Http\Controllers\SmsController::class, 'chooseProvider'])->name('sms.select');
    // Place inbox BEFORE provider route to avoid being captured by /sms/{provider}
    Route::get('/sms/inbox', [\App\Http\Controllers\SmsController::class, 'inbox'])->name('sms.inbox');
    // Constrain provider param to allowed values
    Route::get('/sms/{provider}', [\App\Http\Controllers\SmsController::class, 'indexProvider'])
        ->where('provider', 'smspool|tigersms|all')
        ->name('sms.index');
    // Direct provider shortcuts
    Route::get('/smspool', fn() => redirect()->route('sms.index', ['provider' => 'smspool']));
    Route::get('/tigersms', fn() => redirect()->route('sms.index', ['provider' => 'tigersms']));
    Route::post('/sms/request-number', [\App\Http\Controllers\SmsController::class, 'requestNumber'])->name('sms.request-number');
    Route::post('/sms/check-status', [\App\Http\Controllers\SmsController::class, 'checkStatus'])->name('sms.check-status');
    Route::post('/sms/pricing', [\App\Http\Controllers\SmsController::class, 'pricing'])->name('sms.pricing');
    Route::post('/sms/service-countries', [\App\Http\Controllers\SmsController::class, 'getServiceCountries'])->name('sms.service-countries');
    Route::get('/sms/test/tigersms', [\App\Http\Controllers\SmsController::class, 'testTiger'])->name('sms.test.tigersms');
    Route::get('/sms/test/tigersms/services', [\App\Http\Controllers\SmsController::class, 'testTigerServices'])->name('sms.test.tigersms.services');
    Route::get('/sms/test/tigersms/countries', [\App\Http\Controllers\SmsController::class, 'testTigerServiceCountries'])->name('sms.test.tigersms.countries');
    Route::get('/sms/test/tigersms/purchase', [\App\Http\Controllers\SmsController::class, 'testTigerPurchase'])->name('sms.test.tigersms.purchase');
    
    // Tickets
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/reply', [TicketController::class, 'reply'])->name('tickets.reply');
    
    // Admin routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', function() {
            return redirect()->route('admin.dashboard');
        });
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::post('/orders/{order}/approve-replacement', [AdminController::class, 'approveReplacement'])->name('orders.approve-replacement');
        
        // Settings
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
        
        // SMS Service
        Route::get('/sms-settings', [AdminController::class, 'smsSettings'])->name('sms-settings');
        Route::post('/sms-settings', [AdminController::class, 'updateSmsSettings'])->name('sms-settings.update');
        
        // Deposit Management
        Route::get('/deposits', [AdminController::class, 'deposits'])->name('deposits');
        Route::post('/deposits/{transaction}/approve', [AdminController::class, 'approveDeposit'])->name('deposits.approve');
        
        // User Management
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::post('/users/{user}/update', [AdminController::class, 'updateUser'])->name('users.update');
        
        // Bulk Upload
        Route::get('/bulk-upload', [AdminController::class, 'bulkUpload'])->name('bulk-upload');
        Route::post('/bulk-upload', [AdminController::class, 'processBulkUpload'])->name('bulk-upload.process');

        // Products & Categories
        Route::get('/products', [AdminController::class, 'products'])->name('products');
        Route::post('/products', [AdminController::class, 'storeProduct'])->name('products.store');
        Route::post('/products/{product}/update', [AdminController::class, 'updateProduct'])->name('products.update');
        Route::post('/products/{product}/details/upload', [AdminController::class, 'uploadProductDetails'])->name('products.details.upload');
        Route::get('/products/demo-format.txt', [AdminController::class, 'downloadDemoTxt'])->name('products.demo.download');

        Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
        Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
        Route::post('/categories/{category}/update', [AdminController::class, 'updateCategory'])->name('categories.update');
        Route::post('/categories/{category}/delete', [AdminController::class, 'deleteCategory'])->name('categories.delete');
    });
});
