@extends('layouts.app')

@section('title', 'User Management - Admin')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 pt-16 md:pt-20 mt-[100px] pb-20 md:pb-8" style="margin-top: 100px;">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl md:text-3xl font-bold gradient-text">User Management</h1>
        <a href="{{ route('admin.dashboard') }}" class="text-yellow-accent hover:text-red-accent transition">← Back</a>
    </div>

    <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-lg p-4 md:p-6">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-dark-300">
                        <th class="text-left py-3 px-2 text-gray-300 text-xs md:text-sm">Name</th>
                        <th class="text-left py-3 px-2 text-gray-300 text-xs md:text-sm">Email</th>
                        <th class="text-left py-3 px-2 text-gray-300 text-xs md:text-sm">Balance</th>
                        <th class="text-left py-3 px-2 text-gray-300 text-xs md:text-sm">Role</th>
                        <th class="text-left py-3 px-2 text-gray-300 text-xs md:text-sm">Joined</th>
                        <th class="text-left py-3 px-2 text-gray-300 text-xs md:text-sm">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr class="border-b border-dark-300">
                        <td class="py-3 px-2 text-gray-300 text-xs md:text-sm">{{ $user->name }}</td>
                        <td class="py-3 px-2 text-gray-300 text-xs md:text-sm">{{ $user->email }}</td>
                        <td class="py-3 px-2 text-yellow-accent font-semibold text-xs md:text-sm">₦{{ number_format($user->wallet->balance ?? 0, 2) }}</td>
                        <td class="py-3 px-2">
                            @if($user->is_admin)
                            <span class="px-2 py-1 rounded text-xs bg-red-accent/20 text-red-accent border border-red-accent/30">Admin</span>
                            @else
                            <span class="px-2 py-1 rounded text-xs bg-gray-600/20 text-gray-400 border border-gray-500/30">User</span>
                            @endif
                        </td>
                        <td class="py-3 px-2 text-gray-400 text-xs md:text-sm">{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="py-3 px-2">
                            <button onclick="editUser({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}', {{ $user->wallet->balance ?? 0 }}, {{ $user->is_admin ? 'true' : 'false' }})" 
                                    class="bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white px-4 py-2 rounded-lg font-semibold transition text-xs md:text-sm shadow-lg shadow-red-accent/30">
                                Edit
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="mt-4">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Edit User Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-[9999] flex items-center justify-center p-4">
    <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-2xl max-w-md w-full p-6">
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
                    <label class="flex items-center cursor-pointer">
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
    
    formData.set('is_admin', document.getElementById('user_is_admin').checked ? '1' : '0');
    
    const btn = this.querySelector('button[type="submit"]');
    const btnText = btn.querySelector('span');
    btn.disabled = true;
    btnText.textContent = 'Saving...';
    
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
            showAlert(data.message || 'Failed to update user', 'error');
            btn.disabled = false;
            btnText.textContent = 'Save';
        }
    } catch (error) {
        showAlert('An error occurred. Please try again.', 'error');
        btn.disabled = false;
        btnText.textContent = 'Save';
    }
});
</script>
@endsection

