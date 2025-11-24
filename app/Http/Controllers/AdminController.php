<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCredential;
use App\Models\ProductDetail;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\DepositReceipt;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->check()) {
                return redirect()->route('login');
            }
            
            if (!auth()->user()->is_admin) {
                abort(403, 'Access denied. Admin privileges required.');
            }
            return $next($request);
        });
    }

    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_orders' => Order::count(),
            'total_products' => Product::count(),
            'open_tickets' => Ticket::where('status', 'open')->count(),
            'replacement_requests' => Order::where('has_replacement_request', true)
                ->where('is_replaced', false)
                ->count(),
        ];

        // Sales statistics
        $salesStats = [
            'total_revenue' => Order::whereIn('status', ['paid', 'completed', 'delivered'])->sum('amount'),
            'today_revenue' => Order::whereIn('status', ['paid', 'completed', 'delivered'])
                ->whereDate('created_at', today())
                ->sum('amount'),
            'this_month_revenue' => Order::whereIn('status', ['paid', 'completed', 'delivered'])
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
            'total_deposits' => \App\Models\Deposit::where('status', 'completed')
                ->sum('amount'),
            'today_deposits' => \App\Models\Deposit::where('status', 'completed')
                ->whereDate('created_at', today())
                ->sum('amount'),
            'pending_deposits' => \App\Models\Deposit::where('status', 'pending')
                ->sum('amount'),
        ];

        $recentOrders = Order::with(['user', 'product'])->latest()->limit(10)->get();
        $pendingReplacements = Order::where('has_replacement_request', true)
            ->where('is_replaced', false)
            ->with(['user', 'product'])
            ->latest()
            ->get();

        return view('admin.dashboard', compact('stats', 'salesStats', 'recentOrders', 'pendingReplacements'));
    }

    public function approveReplacement(Order $order)
    {
        if (!$order->has_replacement_request || $order->is_replaced) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid replacement request.',
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Load the order with its current credential/detail
            $order->load(['credential', 'productDetail']);
            
            // Keep old credential/detail marked as sold (bad log that should not be repurchased)
            $oldCredential = $order->credential;
            $oldProductDetail = $order->productDetail;
            
            // Find new credential/detail for same product
            $newProductDetail = ProductDetail::where('product_id', $order->product_id)
                ->where('is_sold', false)
                ->lockForUpdate()
                ->first();

            if ($newProductDetail) {
                // Using new ProductDetail system
                $order->update([
                    'product_detail_id' => $newProductDetail->id,
                    'credential_id' => null, // Clear old credential reference
                    'is_replaced' => true,
                    'has_replacement_request' => false,
                ]);

                // Mark new detail as sold
                $newProductDetail->update(['is_sold' => true]);
                
            } else {
                // Fallback to old ProductCredential system
                $newCredential = ProductCredential::where('product_id', $order->product_id)
                    ->where('is_sold', false)
                    ->lockForUpdate()
                    ->first();

                if (!$newCredential) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'No available stock for replacement.',
                    ], 400);
                }

                // Update order with new credential
                $order->update([
                    'credential_id' => $newCredential->id,
                    'product_detail_id' => null, // Clear old detail reference
                    'is_replaced' => true,
                    'has_replacement_request' => false,
                ]);

                // Mark new credential as sold
                $newCredential->update([
                    'is_sold' => true,
                    'sold_to_order_id' => $order->id,
                ]);
            }

            // Generate new PIN
            $pin = \App\Models\OrderPin::generatePin();
            \App\Models\OrderPin::updateOrCreate(
                ['order_id' => $order->id],
                ['pin' => $pin, 'is_used' => false]
            );

            // Update related ticket
            $ticket = Ticket::where('order_id', $order->id)
                ->where('is_replacement_request', true)
                ->first();
            
            if ($ticket) {
                $ticket->replies()->create([
                    'user_id' => auth()->id(),
                    'is_admin' => true,
                    'message' => 'Your replacement log has been processed! Check your order to reveal the new credentials with your PIN.',
                ]);
                $ticket->update(['status' => 'resolved']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Replacement approved and new log delivered!',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Replacement approval failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process replacement. Please try again.',
            ], 500);
        }
    }

    // Settings Management
    public function settings()
    {
        $settings = [
            'maintenance_mode' => Setting::get('maintenance_mode', false),
            'maintenance_message' => Setting::get('maintenance_message', ''),
            'site_name' => Setting::get('site_name', 'BiggestLogs'),
            'site_email' => Setting::get('site_email', ''),
            'manual_payment_enabled' => Setting::get('manual_payment_enabled', false),
            'manual_payment_instructions' => Setting::get('manual_payment_instructions', ''),
        ];

        return view('admin.settings', compact('settings'));
    }

    // SMS Service Management
    public function smsSettings()
    {
        $smsService = new SmsService();
        $balance = $smsService->getBalance();
        $services = $smsService->getServices();
        $activeProvider = Setting::get('sms_active_provider', 'smspool');
        
        $settings = [
            'sms_smspool_api_key' => Setting::get('sms_smspool_api_key', ''),
            'sms_tigersms_api_key' => Setting::get('sms_tigersms_api_key', ''),
            'sms_tigersms_base_url' => Setting::get('sms_tigersms_base_url', ''),
            'sms_active_provider' => $activeProvider,
            'sms_coming_soon' => Setting::get('sms_coming_soon', false),
        ];

        return view('admin.sms-settings', compact('settings', 'balance', 'services', 'activeProvider'));
    }

    public function updateSmsSettings(Request $request)
    {
        $request->validate([
            'sms_smspool_api_key' => 'nullable|string',
            'sms_tigersms_api_key' => 'nullable|string',
            'sms_tigersms_base_url' => 'nullable|string',
            'sms_active_provider' => 'nullable|string|in:smspool,tigersms',
            'sms_coming_soon' => 'nullable|boolean',
            'test_connection' => 'nullable|boolean',
        ]);

        if ($request->has('sms_smspool_api_key')) {
            Setting::set('sms_smspool_api_key', $request->sms_smspool_api_key);
        }
        if ($request->has('sms_tigersms_api_key')) {
            Setting::set('sms_tigersms_api_key', $request->sms_tigersms_api_key);
        }
        if ($request->has('sms_tigersms_base_url')) {
            Setting::set('sms_tigersms_base_url', $request->sms_tigersms_base_url);
        }

        if ($request->has('sms_active_provider')) {
            Setting::set('sms_active_provider', $request->sms_active_provider);
        }

        if ($request->has('sms_coming_soon')) {
            Setting::set('sms_coming_soon', $request->boolean('sms_coming_soon'));
        }

        // Clear SMS cache when settings change
        $smsService = new SmsService();
        $smsService->clearCache();

        // Test connection if requested
        if ($request->boolean('test_connection')) {
            // Determine which provider to test based on what API key was sent
            if ($request->has('sms_tigersms_api_key')) {
                // Temporarily switch to tigersms for testing
                Setting::set('sms_active_provider', 'tigersms');
                $smsService = new SmsService();
                $testResult = $smsService->testConnection();
                // Restore original provider
                $activeProvider = Setting::get('sms_active_provider', 'smspool');
                Setting::set('sms_active_provider', $activeProvider);
            } else {
                // Default to smspool
                Setting::set('sms_active_provider', 'smspool');
                $smsService = new SmsService();
                $testResult = $smsService->testConnection();
            }
            
            return response()->json([
                'success' => $testResult['success'],
                'message' => $testResult['message'],
                'test_result' => $testResult,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'SMS settings updated successfully!',
        ]);
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'maintenance_mode' => 'nullable|boolean',
            'maintenance_message' => 'nullable|string',
            'site_name' => 'nullable|string|max:255',
            'site_email' => 'nullable|email',
            'manual_payment_enabled' => 'nullable|boolean',
            'manual_payment_instructions' => 'nullable|string',
        ]);

        foreach ($request->only([
            'maintenance_mode', 'maintenance_message', 'site_name', 
            'site_email', 'manual_payment_enabled', 'manual_payment_instructions'
        ]) as $key => $value) {
            if ($value !== null) {
                Setting::set($key, $value);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully!',
        ]);
    }

    // Deposit Management
    public function deposits()
    {
        $deposits = \App\Models\Deposit::with(['user', 'wallet'])
            ->latest()
            ->paginate(20);

        return view('admin.deposits', compact('deposits'));
    }

    public function approveDeposit(\App\Models\Deposit $deposit)
    {
        // Only reject if deposit is already completed
        // Allow pending and failed deposits to be approved
        if ($deposit->status === 'completed' && $deposit->completed_at !== null) {
            return response()->json([
                'success' => false,
                'message' => 'This deposit has already been processed.',
            ], 400);
        }

        // Check if wallet was already credited (prevent double-crediting)
        $wallet = $deposit->wallet ?? \App\Models\Wallet::firstOrCreate(
            ['user_id' => $deposit->user_id],
            [
                'balance' => 0,
                'total_deposited' => 0,
                'total_withdrawn' => 0,
            ]
        );

        // Only credit if deposit is not already completed
        if ($deposit->status !== 'completed' || $deposit->completed_at === null) {
            DB::transaction(function () use ($deposit, $wallet) {
                $deposit->update([
                    'status' => 'completed',
                    'wallet_id' => $wallet->id,
                    'completed_at' => now(),
                    'description' => $deposit->status === 'failed' 
                        ? 'Deposit approved (previously failed)' 
                        : ($deposit->description ?? 'Deposit approved'),
                ]);
                
                $wallet->increment('balance', $deposit->amount);
                $wallet->increment('total_deposited', $deposit->amount);
                
                // Update linked transaction if exists
                if ($deposit->transaction_id) {
                    $transaction = \App\Models\Transaction::find($deposit->transaction_id);
                    if ($transaction) {
                        $transaction->update(['status' => 'completed']);
                    }
                }
            });
        }

        // Send receipt email
        try {
            $deposit->load('user'); // Ensure user relationship is loaded
            Mail::to($deposit->user->email)->send(new DepositReceipt($deposit->fresh()));
        } catch (\Exception $e) {
            Log::error('Deposit receipt email failed', ['error' => $e->getMessage(), 'deposit_id' => $deposit->id]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Deposit approved and wallet funded!',
        ]);
    }

    // User Management
    public function users()
    {
        $users = User::with('wallet')->latest()->paginate(20);
        return view('admin.users', compact('users'));
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'is_admin' => 'nullable|boolean',
            'balance' => 'nullable|numeric|min:0',
        ]);

        $user->update($request->only(['name', 'email', 'is_admin']));

        if ($request->has('balance')) {
            $wallet = $user->wallet ?? Wallet::create(['user_id' => $user->id]);
            $wallet->update(['balance' => $request->balance]);
        }

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully!',
        ]);
    }

    // Products & Categories Management
    public function categories()
    {
        $categories = Category::orderBy('sort_order')->get();
        return view('admin.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);
        if (empty($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
        }
        $data['is_active'] = (bool)($data['is_active'] ?? true);
        Category::create($data);
        return back()->with('success', 'Category created');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);
        if (empty($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
        }
        $data['is_active'] = (bool)($data['is_active'] ?? $category->is_active);
        $category->update($data);
        return back()->with('success', 'Category updated');
    }

    public function deleteCategory(Category $category)
    {
        $category->delete();
        return back()->with('success', 'Category deleted');
    }

    public function products()
    {
        $products = Product::with('category')->latest()->get();
        $categories = Category::orderBy('sort_order')->get();
        return view('admin.products', compact('products', 'categories'));
    }

    public function storeProduct(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        if (empty($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
        }
        $data['is_active'] = (bool)($data['is_active'] ?? true);
        Product::create($data);
        return back()->with('success', 'Product created');
    }

    public function updateProduct(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $product->id,
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        if (empty($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
        }
        $data['is_active'] = (bool)($data['is_active'] ?? $product->is_active);
        $product->update($data);
        return back()->with('success', 'Product updated');
    }

    public function uploadProductDetails(Request $request, Product $product)
    {
        $request->validate([
            'accounts_file' => 'required|file|mimes:txt',
        ]);

        $file = $request->file('accounts_file');
        $content = trim($file->get());
        $lines = preg_split("/\r\n|\r|\n/", $content);

        $insert = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;
            $insert[] = [
                'product_id' => $product->id,
                'details' => $line,
                'is_sold' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($insert)) {
            DB::table('product_details')->insert($insert);
        }

        return back()->with('success', 'Stock updated: ' . count($insert) . ' lines added');
    }

    public function downloadDemoTxt()
    {
        $demoContent = "Username:username1 | Password:password1\nUsername:username2 | Password:password2\nUsername:username3 | Password:password3";
        return response($demoContent)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="product-accounts-demo.txt"');
    }

    // Bulk Log Upload
    public function bulkUpload()
    {
        $products = Product::where('is_active', true)->get();
        return view('admin.bulk-upload', compact('products'));
    }

    public function processBulkUpload(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $content = file_get_contents($file->getRealPath());
        $lines = array_filter(array_map('trim', explode("\n", $content)));

        $imported = 0;
        $skipped = 0;

        foreach ($lines as $line) {
            // Format: username,password,email (optional)
            $parts = str_getcsv($line);
            
            if (count($parts) < 2) {
                $skipped++;
                continue;
            }

            $username = trim($parts[0]);
            $password = trim($parts[1]);
            $email = isset($parts[2]) ? trim($parts[2]) : null;

            if (empty($username) || empty($password)) {
                $skipped++;
                continue;
            }

            ProductCredential::create([
                'product_id' => $request->product_id,
                'username' => $username,
                'password' => $password,
                'email' => $email,
                'is_sold' => false,
            ]);

            $imported++;
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully imported {$imported} credentials. {$skipped} lines skipped.",
        ]);
    }
}


