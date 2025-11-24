@extends('layouts.app')

@section('title', 'User Details - Admin')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 pt-16 md:pt-20 mt-[100px] pb-20 md:pb-8" style="margin-top: 100px;">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl md:text-3xl font-bold gradient-text">User Account Details</h1>
        <a href="{{ route('admin.users') }}" class="text-yellow-accent hover:text-red-accent transition">← Back to Users</a>
    </div>

    <!-- User Info Card -->
    <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-lg p-4 md:p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h2 class="text-lg md:text-xl font-bold mb-4 text-gray-200">Basic Information</h2>
                <div class="space-y-3">
                    <div>
                        <span class="text-gray-400 text-sm">Name:</span>
                        <span class="text-gray-200 font-semibold ml-2">{{ $user->name }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 text-sm">Email:</span>
                        <span class="text-gray-200 font-semibold ml-2">{{ $user->email }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 text-sm">Phone:</span>
                        <span class="text-gray-200 font-semibold ml-2">{{ $user->phone ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 text-sm">Role:</span>
                        @if($user->is_admin)
                            <span class="px-2 py-1 rounded text-xs bg-red-accent/20 text-red-accent border border-red-accent/30 ml-2">Admin</span>
                        @else
                            <span class="px-2 py-1 rounded text-xs bg-gray-600/20 text-gray-400 border border-gray-500/30 ml-2">User</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-gray-400 text-sm">Joined:</span>
                        <span class="text-gray-200 font-semibold ml-2">{{ $user->created_at->format('M d, Y h:i A') }}</span>
                    </div>
                </div>
            </div>
            <div>
                <h2 class="text-lg md:text-xl font-bold mb-4 text-gray-200">Wallet Information</h2>
                <div class="space-y-3">
                    <div>
                        <span class="text-gray-400 text-sm">Balance:</span>
                        <span class="text-yellow-accent font-bold text-xl ml-2">₦{{ number_format($user->wallet->balance ?? 0, 2) }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 text-sm">Total Deposited:</span>
                        <span class="text-green-400 font-semibold ml-2">₦{{ number_format($user->wallet->total_deposited ?? 0, 2) }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 text-sm">Total Withdrawn:</span>
                        <span class="text-red-400 font-semibold ml-2">₦{{ number_format($user->wallet->total_withdrawn ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Deposits -->
        <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-lg p-4 md:p-6">
            <h3 class="text-lg font-bold mb-4 text-gray-200">Recent Deposits</h3>
            @if($recentDeposits->count() > 0)
                <div class="space-y-2">
                    @foreach($recentDeposits as $deposit)
                        <div class="flex justify-between items-center py-2 border-b border-dark-300">
                            <div>
                                <span class="text-gray-300 text-sm">{{ $deposit->gateway }}</span>
                                <span class="text-gray-400 text-xs block">{{ $deposit->created_at->format('M d, Y') }}</span>
                            </div>
                            <span class="text-yellow-accent font-semibold">₦{{ number_format($deposit->amount, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-sm">No deposits yet</p>
            @endif
        </div>

        <!-- Recent Orders -->
        <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-lg p-4 md:p-6">
            <h3 class="text-lg font-bold mb-4 text-gray-200">Recent Orders</h3>
            @if($recentOrders->count() > 0)
                <div class="space-y-2">
                    @foreach($recentOrders as $order)
                        <div class="flex justify-between items-center py-2 border-b border-dark-300">
                            <div>
                                <span class="text-gray-300 text-sm">{{ $order->order_number ?? '#' . $order->id }}</span>
                                <span class="text-gray-400 text-xs block">{{ $order->created_at->format('M d, Y') }}</span>
                            </div>
                            <span class="text-yellow-accent font-semibold">₦{{ number_format($order->total ?? 0, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-sm">No orders yet</p>
            @endif
        </div>

        <!-- Recent Transactions -->
        <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-lg p-4 md:p-6">
            <h3 class="text-lg font-bold mb-4 text-gray-200">Recent Transactions</h3>
            @if($recentTransactions->count() > 0)
                <div class="space-y-2">
                    @foreach($recentTransactions as $transaction)
                        <div class="flex justify-between items-center py-2 border-b border-dark-300">
                            <div>
                                <span class="text-gray-300 text-sm">{{ ucfirst($transaction->type) }}</span>
                                <span class="text-gray-400 text-xs block">{{ $transaction->created_at->format('M d, Y') }}</span>
                            </div>
                            <span class="text-yellow-accent font-semibold">₦{{ number_format($transaction->amount, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-sm">No transactions yet</p>
            @endif
        </div>
    </div>

    <!-- Edit User Button -->
    <div class="mt-6">
        <button onclick="editUser({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}', {{ $user->wallet->balance ?? 0 }}, {{ $user->is_admin ? 'true' : 'false' }})" 
                class="bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white px-6 py-3 rounded-lg font-semibold transition shadow-lg shadow-red-accent/30">
            Edit User
        </button>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-[9999] flex items-center justify-center p-4">
    <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-2xl p-6 md:p-8 max-w-md w-full">
        <h3 class="text-xl font-bold mb-4 gradient-text">Edit User</h3>
        <form id="edit-user-form">
            @csrf
            <input type="hidden" id="user_id" name="user_id">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-300">Name</label>
                    <input type="text" id="user_name" name="name" required
                           class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-300">Email</label>
                    <input type="email" id="user_email" name="email" required
                           class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-300">Balance</label>
                    <input type="number" id="user_balance" name="balance" step="0.01" min="0" required
                           class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none">
                </div>
                <div class="flex items-center">
                    <label class="flex items-center">
                        <input type="checkbox" id="user_is_admin" name="is_admin" value="1" 
                               class="w-4 h-4 rounded border-dark-400 bg-dark-300 text-yellow-accent focus:ring-yellow-accent focus:ring-2">
                        <span class="ml-2 text-sm text-gray-300">Admin Access</span>
                    </label>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="flex-1 bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white py-3 rounded-xl font-semibold transition glow-button relative shadow-lg shadow-red-accent/40">
                    <span class="relative z-10">Save</span>
                </button>
                <button type="button" onclick="closeModal()" class="flex-1 bg-dark-300 border border-dark-400 text-gray-300 py-3 rounded-xl font-semibold transition hover:bg-dark-400">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
function editUser(id, name, email, balance, isAdmin) {
    document.getElementById('user_id').value = id;
    document.getElementById('user_name').value = name;
    document.getElementById('user_email').value = email;
    document.getElementById('user_balance').value = balance;
    document.getElementById('user_is_admin').checked = isAdmin;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('editModal').classList.add('hidden');
}

document.getElementById('edit-user-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const userId = formData.get('user_id');
    
    try {
        const response = await fetch(`/admin/users/${userId}/update`, {
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
});
</script>
@endsection

