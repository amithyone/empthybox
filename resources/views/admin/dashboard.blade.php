@extends('layouts.app')

@section('title', 'Admin Dashboard - BiggestLogs')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 py-6 md:py-8 pb-20 md:pb-8">
    <h1 class="text-2xl md:text-3xl font-bold mb-4 md:mb-6 gradient-text">Admin Dashboard</h1>

    <!-- Quick Actions -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mb-6 md:mb-8">
        <a href="{{ route('admin.users') }}" class="bg-dark-200 border-2 border-dark-300 p-4 md:p-6 rounded-xl shadow-lg hover:border-yellow-accent/50 transition text-center">
            <div class="text-2xl md:text-3xl mb-2">👥</div>
            <div class="font-semibold text-sm md:text-base text-gray-300">Users</div>
        </a>
        <a href="{{ route('admin.deposits') }}" class="bg-dark-200 border-2 border-dark-300 p-4 md:p-6 rounded-xl shadow-lg hover:border-yellow-accent/50 transition text-center">
            <div class="text-2xl md:text-3xl mb-2">💰</div>
            <div class="font-semibold text-sm md:text-base text-gray-300">Deposits</div>
        </a>
        <a href="{{ route('admin.bulk-upload') }}" class="bg-dark-200 border-2 border-dark-300 p-4 md:p-6 rounded-xl shadow-lg hover:border-yellow-accent/50 transition text-center">
            <div class="text-2xl md:text-3xl mb-2">📤</div>
            <div class="font-semibold text-sm md:text-base text-gray-300">Bulk Upload</div>
        </a>
        <a href="{{ route('admin.sms-settings') }}" class="bg-dark-200 border-2 border-dark-300 p-4 md:p-6 rounded-xl shadow-lg hover:border-yellow-accent/50 transition text-center">
            <div class="text-2xl md:text-3xl mb-2">📱</div>
            <div class="font-semibold text-sm md:text-base text-gray-300">SMS Service</div>
        </a>
        <a href="{{ route('admin.settings') }}" class="bg-dark-200 border-2 border-dark-300 p-4 md:p-6 rounded-xl shadow-lg hover:border-yellow-accent/50 transition text-center">
            <div class="text-2xl md:text-3xl mb-2">⚙️</div>
            <div class="font-semibold text-sm md:text-base text-gray-300">Settings</div>
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 md:gap-4 mb-6 md:mb-8">
        <div class="bg-dark-200 border-2 border-dark-300 p-4 md:p-6 rounded-xl shadow-lg hover:border-yellow-accent/50 transition">
            <div class="text-xl md:text-2xl font-bold bg-gradient-to-r from-red-accent to-yellow-accent bg-clip-text text-transparent">{{ $stats['total_users'] }}</div>
            <div class="text-xs md:text-sm text-gray-400 mt-1">Total Users</div>
        </div>
        <div class="bg-dark-200 border-2 border-dark-300 p-4 md:p-6 rounded-xl shadow-lg hover:border-yellow-accent/50 transition">
            <div class="text-xl md:text-2xl font-bold bg-gradient-to-r from-red-accent to-yellow-accent bg-clip-text text-transparent">{{ $stats['total_orders'] }}</div>
            <div class="text-xs md:text-sm text-gray-400 mt-1">Total Orders</div>
        </div>
        <div class="bg-dark-200 border-2 border-dark-300 p-4 md:p-6 rounded-xl shadow-lg hover:border-yellow-accent/50 transition">
            <div class="text-xl md:text-2xl font-bold text-green-400">{{ $stats['total_products'] }}</div>
            <div class="text-xs md:text-sm text-gray-400 mt-1">Products</div>
        </div>
        <div class="bg-dark-200 border-2 border-dark-300 p-4 md:p-6 rounded-xl shadow-lg hover:border-yellow-accent/50 transition">
            <div class="text-xl md:text-2xl font-bold text-yellow-accent">{{ $stats['open_tickets'] }}</div>
            <div class="text-xs md:text-sm text-gray-400 mt-1">Open Tickets</div>
        </div>
        <div class="bg-dark-200 border-2 border-dark-300 p-4 md:p-6 rounded-xl shadow-lg hover:border-yellow-accent/50 transition">
            <div class="text-xl md:text-2xl font-bold text-red-accent">{{ $stats['replacement_requests'] }}</div>
            <div class="text-xs md:text-sm text-gray-400 mt-1">Replacements</div>
        </div>
    </div>

    <!-- Pending Replacements -->
    @if($pendingReplacements->count() > 0)
    <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-lg p-4 md:p-6 mb-6 md:mb-8">
        <h2 class="text-lg md:text-xl font-bold mb-4 text-gray-200">Pending Replacement Requests</h2>
        <div class="space-y-4">
            @foreach($pendingReplacements as $order)
            <div class="border-l-4 border-yellow-accent pl-4 py-2 bg-dark-300/30 rounded-r-lg">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <p class="font-semibold text-gray-200">{{ $order->product->name }}</p>
                        <p class="text-xs md:text-sm text-gray-400">Order #{{ $order->order_number }} - {{ $order->user->name }}</p>
                        <p class="text-xs text-gray-500">{{ $order->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                    <button onclick="approveReplacement({{ $order->id }})" 
                            class="bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white px-4 py-2 rounded-xl font-semibold transition shadow-lg shadow-red-accent/30 self-start sm:self-auto text-sm md:text-base">
                        Approve Replacement
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Recent Orders -->
    <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-lg p-4 md:p-6">
        <h2 class="text-lg md:text-xl font-bold mb-4 text-gray-200">Recent Orders</h2>
        @if($recentOrders->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-dark-300">
                        <th class="text-left py-2 text-gray-300 text-xs md:text-sm">Order #</th>
                        <th class="text-left py-2 text-gray-300 text-xs md:text-sm">User</th>
                        <th class="text-left py-2 text-gray-300 text-xs md:text-sm">Product</th>
                        <th class="text-left py-2 text-gray-300 text-xs md:text-sm">Amount</th>
                        <th class="text-left py-2 text-gray-300 text-xs md:text-sm">Status</th>
                        <th class="text-left py-2 text-gray-300 text-xs md:text-sm">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentOrders as $order)
                    <tr class="border-b border-dark-300">
                        <td class="py-2 text-gray-300 text-xs md:text-sm">{{ $order->order_number }}</td>
                        <td class="py-2 text-gray-300 text-xs md:text-sm">{{ $order->user->name }}</td>
                        <td class="py-2 text-gray-300 text-xs md:text-sm">{{ $order->product->name }}</td>
                        <td class="py-2 text-yellow-accent text-xs md:text-sm font-semibold">${{ number_format($order->amount, 2) }}</td>
                        <td class="py-2">
                            <span class="px-2 py-1 rounded text-xs border
                                {{ $order->status === 'completed' ? 'bg-green-600/20 text-green-400 border-green-500/30' : 'bg-yellow-accent/20 text-yellow-accent border-yellow-accent/30' }}">
                                {{ ucfirst($order->status ?? 'pending') }}
                            </span>
                        </td>
                        <td class="py-2 text-gray-400 text-xs md:text-sm">{{ $order->created_at->format('M d, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-gray-400 text-center py-8">No orders yet.</p>
        @endif
    </div>
</div>

@section('scripts')
<script>
async function approveReplacement(orderId) {
    if (!confirm('Approve replacement for this order?')) return;
    
    try {
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        
        const response = await fetch(`/admin/orders/${orderId}/approve-replacement`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.message, 'error');
        }
    } catch (error) {
        showAlert('An error occurred. Please try again.', 'error');
    }
}
</script>
@endsection
