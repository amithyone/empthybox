@extends('layouts.app')

@section('title', 'Manual Payment - BiggestLogs')

@section('content')
<div class="max-w-4xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 pt-16 md:pt-20 pb-20 md:pb-8">
    <div class="flex items-center justify-between mb-4 md:mb-6">
        <h1 class="text-2xl md:text-3xl font-bold gradient-text">Manual Payment 💳</h1>
        <a href="{{ route('dashboard') }}" class="text-yellow-accent hover:text-red-accent transition flex items-center gap-2">
            <span>← Back</span>
        </a>
    </div>

    <!-- Payment Amount -->
    <div class="bg-gradient-to-r from-yellow-accent to-red-accent rounded-xl shadow-lg p-6 md:p-8 mb-6 text-center">
        <p class="text-gray-900 mb-2 font-semibold">Amount to Pay</p>
        <p class="text-4xl md:text-5xl font-bold text-dark-900">₦{{ number_format($amount, 2) }}</p>
    </div>

    @if($bankingDetails->count() > 0)
        <!-- Banking Details Section -->
        <div class="bg-dark-200 rounded-xl shadow-lg p-6 md:p-8 mb-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-200">💰 Payment Instructions</h2>
            <p class="text-gray-400 mb-4">Please transfer the exact amount above to one of our bank accounts below:</p>
            
            @foreach($bankingDetails as $bank)
            <div class="bg-dark-300 border-2 border-yellow-accent/30 rounded-lg p-4 md:p-6 mb-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-bold text-yellow-accent">{{ $bank->bank_name }}</h3>
                    <span class="px-3 py-1 bg-green-500/20 text-green-400 rounded-full text-xs font-semibold">{{ strtoupper($bank->account_type) }}</span>
                </div>
                
                <div class="space-y-2 mb-4">
                    <div class="flex items-center gap-3">
                        <span class="text-gray-400 w-32">Account Name:</span>
                        <span class="text-gray-200 font-semibold">{{ $bank->account_name }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-gray-400 w-32">Account Number:</span>
                        <span class="text-yellow-accent font-bold text-xl">{{ $bank->account_number }}</span>
                        <button onclick="copyToClipboard('{{ $bank->account_number }}')" class="ml-2 px-3 py-1 bg-yellow-accent text-dark-900 rounded text-sm font-semibold hover:bg-yellow-500 transition">
                            Copy
                        </button>
                    </div>
                    @if($bank->swift_code)
                    <div class="flex items-center gap-3">
                        <span class="text-gray-400 w-32">SWIFT Code:</span>
                        <span class="text-gray-200 font-semibold">{{ $bank->swift_code }}</span>
                    </div>
                    @endif
                    @if($bank->routing_number)
                    <div class="flex items-center gap-3">
                        <span class="text-gray-400 w-32">Routing Number:</span>
                        <span class="text-gray-200 font-semibold">{{ $bank->routing_number }}</span>
                    </div>
                    @endif
                </div>

                @if($bank->instructions)
                <div class="bg-blue-500/20 border border-blue-500/50 rounded-lg p-3 mt-4">
                    <p class="text-blue-300 text-sm">{{ $bank->instructions }}</p>
                </div>
                @endif
            </div>
            @endforeach
        </div>

        <!-- Upload Receipt Form -->
        <div class="bg-dark-200 rounded-xl shadow-lg p-6 md:p-8 mb-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-200">📤 Upload Payment Receipt</h2>
            <p class="text-gray-400 mb-4">After making the transfer, please upload your payment receipt below:</p>
            
            <form id="manual-payment-form" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="amount" value="{{ $amount }}">
                @if(isset($orderId))
                    <input type="hidden" name="order_id" value="{{ $orderId }}">
                @endif
                @if(isset($product))
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                @endif

                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2 text-gray-300">Select Bank Account Used *</label>
                    <select name="banking_detail_id" required class="w-full bg-dark-300 border-2 border-dark-400 rounded-lg px-4 py-3 text-gray-200 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/50">
                        <option value="">Choose the bank account you transferred to</option>
                        @foreach($bankingDetails as $bank)
                            <option value="{{ $bank->id }}">
                                {{ $bank->bank_name }} - {{ $bank->account_name }} ({{ $bank->account_number }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2 text-gray-300">Upload Payment Receipt *</label>
                    <input type="file" name="receipt" accept="image/*,.pdf" required class="w-full bg-dark-300 border-2 border-dark-400 rounded-lg px-4 py-3 text-gray-200 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-yellow-accent file:text-dark-900 hover:file:bg-yellow-500">
                    <p class="mt-2 text-xs text-gray-400">Accepted formats: JPG, PNG, PDF (Max: 5MB)</p>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2 text-gray-300">Additional Notes (Optional)</label>
                    <textarea name="notes" rows="3" class="w-full bg-dark-300 border-2 border-dark-400 rounded-lg px-4 py-3 text-gray-200 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/50" placeholder="Add any additional information about your payment (transaction reference, etc.)..."></textarea>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-yellow-accent to-red-accent hover:from-yellow-500 hover:to-red-500 text-dark-900 font-bold py-3 px-6 rounded-lg transition shadow-lg">
                    Submit Payment Receipt
                </button>
            </form>
        </div>

        <!-- Important Instructions -->
        <div class="bg-blue-500/20 border-2 border-blue-500 rounded-lg p-4 md:p-6">
            <p class="font-semibold mb-3 text-blue-300 flex items-center gap-2">
                <span>📋</span> Important Instructions
            </p>
            <ul class="list-disc list-inside space-y-2 text-sm text-blue-200">
                <li>Transfer the <strong>exact amount</strong> shown above (₦{{ number_format($amount, 2) }})</li>
                <li>Use one of the bank accounts listed above</li>
                <li>After transfer, upload a clear screenshot or photo of your payment receipt</li>
                <li>Your payment will be reviewed and approved within 24 hours</li>
                <li>You will receive a notification once your payment is approved</li>
                <li>If you have any questions, contact our support team</li>
            </ul>
        </div>
    @else
        <div class="bg-red-500/20 border-2 border-red-500 rounded-lg p-6 text-center">
            <p class="text-red-400 font-semibold text-lg mb-2">⚠️ Manual Payment Unavailable</p>
            <p class="text-red-300">No banking details are currently configured. Please contact support or use another payment method.</p>
        </div>
    @endif
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Account number copied to clipboard!');
    }, function(err) {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('Account number copied to clipboard!');
    });
}

document.getElementById('manual-payment-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting...';
    
    try {
        const response = await fetch('{{ route("manual-payment.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message || 'Payment receipt uploaded successfully!');
            window.location.href = data.redirect || '{{ route("manual-payment.index") }}';
        } else {
            alert(data.message || 'An error occurred. Please try again.');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    } catch (error) {
        alert('An error occurred. Please try again.');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
});
</script>
@endsection

