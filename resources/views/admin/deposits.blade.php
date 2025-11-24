@extends('layouts.app')

@section('title', 'Deposit Management - Admin')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 pt-16 md:pt-20 mt-[100px] pb-20 md:pb-8" style="margin-top: 100px;">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl md:text-3xl font-bold gradient-text">Deposit Management</h1>
        <a href="{{ route('admin.dashboard') }}" class="text-yellow-accent hover:text-red-accent transition">← Back</a>
    </div>

    @if($deposits->count() > 0)
    <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-lg p-4 md:p-6">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-dark-300">
                        <th class="text-left py-3 px-2 text-gray-300 text-xs md:text-sm">Date</th>
                        <th class="text-left py-3 px-2 text-gray-300 text-xs md:text-sm">User</th>
                        <th class="text-left py-3 px-2 text-gray-300 text-xs md:text-sm">Amount</th>
                        <th class="text-left py-3 px-2 text-gray-300 text-xs md:text-sm">Gateway</th>
                        <th class="text-left py-3 px-2 text-gray-300 text-xs md:text-sm">Status</th>
                        <th class="text-left py-3 px-2 text-gray-300 text-xs md:text-sm">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($deposits as $deposit)
                    <tr class="border-b border-dark-300">
                        <td class="py-3 px-2 text-gray-300 text-xs md:text-sm">{{ $deposit->created_at->format('M d, Y h:i A') }}</td>
                        <td class="py-3 px-2 text-gray-300 text-xs md:text-sm">{{ $deposit->user->name }}</td>
                        <td class="py-3 px-2 text-yellow-accent font-semibold text-xs md:text-sm">₦{{ number_format($deposit->amount, 2) }}</td>
                        <td class="py-3 px-2 text-gray-400 text-xs md:text-sm">{{ ucfirst($deposit->gateway ?? 'N/A') }}</td>
                        <td class="py-3 px-2">
                            <span class="px-2 py-1 rounded text-xs border
                                {{ $deposit->status === 'completed' ? 'bg-green-600/20 text-green-400 border-green-500/30' : ($deposit->status === 'failed' ? 'bg-red-600/20 text-red-400 border-red-500/30' : 'bg-yellow-accent/20 text-yellow-accent border-yellow-accent/30') }}">
                                {{ ucfirst($deposit->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-2">
                            @if($deposit->status === 'pending' || $deposit->status === 'failed')
                            <button onclick="approveDeposit({{ $deposit->id }})" 
                                    class="bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white px-4 py-2 rounded-lg font-semibold transition text-xs md:text-sm shadow-lg shadow-red-accent/30">
                                {{ $deposit->status === 'failed' ? 'Re-approve' : 'Approve' }}
                            </button>
                            @elseif($deposit->status === 'completed')
                            <span class="text-gray-500 text-xs md:text-sm">Completed</span>
                            @else
                            <span class="text-gray-500 text-xs md:text-sm">{{ ucfirst($deposit->status) }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($deposits->hasPages())
        <div class="mt-4">
            {{ $deposits->links() }}
        </div>
        @endif
    </div>
    @else
    <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-lg p-8 md:p-12 text-center">
        <p class="text-gray-400 text-lg">No deposits yet.</p>
    </div>
    @endif
</div>

@section('scripts')
<script>
async function approveDeposit(transactionId) {
    if (!confirm('Approve this deposit and credit the user\'s wallet?')) return;
    
    try {
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        
        const response = await fetch(`/admin/deposits/${transactionId}/approve`, {
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

