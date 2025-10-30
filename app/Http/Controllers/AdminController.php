<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCredential;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $recentOrders = Order::with(['user', 'product'])->latest()->limit(10)->get();
        $pendingReplacements = Order::where('has_replacement_request', true)
            ->where('is_replaced', false)
            ->with(['user', 'product'])
            ->latest()
            ->get();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'pendingReplacements'));
    }

    public function approveReplacement(Order $order)
    {
        if (!$order->has_replacement_request || $order->is_replaced) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid replacement request.',
            ], 400);
        }

        // Find new credential for same product
        $newCredential = ProductCredential::where('product_id', $order->product_id)
            ->where('is_sold', false)
            ->first();

        if (!$newCredential) {
            return response()->json([
                'success' => false,
                'message' => 'No available credentials for replacement.',
            ], 400);
        }

        // Update order with new credential
        $order->update([
            'credential_id' => $newCredential->id,
            'is_replaced' => true,
            'has_replacement_request' => false,
        ]);

        // Mark new credential as sold
        $newCredential->update([
            'is_sold' => true,
            'sold_to_order_id' => $order->id,
        ]);

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

        return response()->json([
            'success' => true,
            'message' => 'Replacement approved and new log delivered!',
        ]);
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
            $smsService = new SmsService();
            $testResult = $smsService->testConnection();
            
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
        $deposits = Transaction::where('type', 'deposit')
            ->with(['user', 'wallet'])
            ->latest()
            ->paginate(20);

        return view('admin.deposits', compact('deposits'));
    }

    public function approveDeposit(Transaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This deposit has already been processed.',
            ], 400);
        }

        DB::transaction(function () use ($transaction) {
            $transaction->update(['status' => 'completed']);
            
            $wallet = $transaction->wallet;
            $wallet->increment('balance', $transaction->amount);
            $wallet->increment('total_deposited', $transaction->amount);
        });

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


