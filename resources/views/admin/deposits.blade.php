@extends('layouts.app')

@section('title', 'Deposit Management - Admin')

@php
use Illuminate\Support\Facades\Storage;
@endphp

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
                        <th class="text-left py-3 px-2 text-gray-300 text-xs md:text-sm">Receipt</th>
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
                            @if($deposit->manualPayment && $deposit->manualPayment->receipt_path)
                                @php
                                    $receiptUrl = Storage::url($deposit->manualPayment->receipt_path);
                                    $isImage = in_array(strtolower(pathinfo($deposit->manualPayment->receipt_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                @endphp
                                <button onclick="openReceiptLightbox('{{ $receiptUrl }}', {{ $isImage ? 'true' : 'false' }})" 
                                        class="inline-flex items-center gap-1 text-yellow-accent hover:text-red-accent transition text-xs md:text-sm cursor-pointer">
                                    <span>📄</span>
                                    <span>View Receipt</span>
                                </button>
                            @elseif($deposit->gateway === 'manual')
                                <span class="text-gray-500 text-xs md:text-sm">No receipt</span>
                            @else
                                <span class="text-gray-500 text-xs md:text-sm">—</span>
                            @endif
                        </td>
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

<!-- Receipt Lightbox Modal -->
<div id="receipt-lightbox" class="hidden fixed inset-0 bg-black/90 backdrop-blur-sm z-[9999] items-center justify-center p-4" onclick="closeReceiptLightbox(event)">
    <div class="relative max-w-4xl w-full max-h-[90vh] bg-dark-200 rounded-xl shadow-2xl overflow-hidden" onclick="event.stopPropagation()">
        <button onclick="closeReceiptLightbox()" class="absolute top-4 right-4 z-10 bg-red-accent hover:bg-red-600 text-white rounded-full w-10 h-10 flex items-center justify-center transition shadow-lg">
            <span class="text-2xl font-bold">&times;</span>
        </button>
        <div id="receipt-content" class="p-4 overflow-auto max-h-[90vh]">
            <!-- Content will be loaded here -->
        </div>
    </div>
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

function openReceiptLightbox(url, isImage) {
    const lightbox = document.getElementById('receipt-lightbox');
    const content = document.getElementById('receipt-content');
    
    if (isImage) {
        content.innerHTML = `<img src="${url}" alt="Receipt" class="w-full h-auto rounded-lg shadow-lg">`;
    } else {
        // For PDFs, use an iframe
        content.innerHTML = `<iframe src="${url}" class="w-full h-[80vh] rounded-lg shadow-lg" frameborder="0"></iframe>`;
    }
    
    lightbox.classList.remove('hidden');
    lightbox.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeReceiptLightbox(event) {
    if (event && event.target.id !== 'receipt-lightbox') {
        return;
    }
    
    const lightbox = document.getElementById('receipt-lightbox');
    lightbox.classList.add('hidden');
    lightbox.classList.remove('flex');
    document.body.style.overflow = '';
}

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeReceiptLightbox();
    }
});
</script>
@endsection

