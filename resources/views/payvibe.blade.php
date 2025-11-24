@extends('layouts.app')

@section('title', 'PayVibe Payment - BiggestLogs')

@section('content')
<div class="max-w-3xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 py-6 md:py-8 pb-20 md:pb-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl md:text-3xl font-bold gradient-text">PayVibe Payment 🔥</h1>
        <a href="{{ route('wallet.index') }}" class="text-yellow-accent hover:text-red-accent transition flex items-center gap-2">
            <span>← Back to Wallet</span>
        </a>
    </div>

    @if(isset($transaction) && isset($virtualAccount))
    <!-- Payment Details -->
    <div class="bg-gradient-to-r from-red-accent via-yellow-accent to-red-accent rounded-xl shadow-2xl shadow-red-accent/30 p-6 md:p-8 text-white mb-6">
        <div class="text-4xl md:text-5xl mb-4">💰</div>
        <h2 class="text-2xl md:text-3xl font-bold mb-2">Payment Instructions</h2>
        <p class="opacity-90 mb-6">Transfer the exact amount to the virtual account below</p>

        <!-- Account Details -->
        <div class="bg-black/20 rounded-lg p-4 mb-4 space-y-3">
            <div class="flex justify-between items-center">
                <span class="opacity-80">Bank Name:</span>
                <span class="font-bold">{{ $bankName }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="opacity-80">Account Name:</span>
                <span class="font-bold">{{ $accountName }}</span>
            </div>
            <div class="flex justify-between items-center border-t border-white/20 pt-3 mt-3">
                <span class="opacity-80">Account Number:</span>
                <span class="font-bold text-2xl copy-text" id="account-number">{{ $virtualAccount }}</span>
            </div>
        </div>

        <button onclick="copyAccountNumber()" class="w-full bg-white/20 hover:bg-white/30 text-white py-3 rounded-lg font-semibold transition">
            📋 Copy Account Number
        </button>
    </div>

    <!-- Amount Details -->
    <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-lg p-6 mb-6">
        <h3 class="text-xl font-bold mb-4 text-gray-200">Payment Summary</h3>
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-gray-400">Amount to Fund:</span>
                <span class="font-bold text-yellow-accent">₦{{ number_format($amount, 2) }}</span>
            </div>
            <div class="flex justify-between items-center border-t border-dark-300 pt-3">
                <span class="text-gray-400">Service Charges:</span>
                <span class="font-bold text-red-accent">₦{{ number_format($charges, 2) }}</span>
            </div>
            <div class="flex justify-between items-center border-t border-dark-300 pt-3">
                <span class="text-lg font-bold text-gray-200">Total to Transfer:</span>
                <span class="text-2xl font-bold gradient-text">₦{{ number_format($amount + $charges, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Reference -->
    <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-lg p-6 mb-6">
        <div class="flex justify-between items-center">
            <span class="text-gray-400">Reference:</span>
            <span class="font-mono font-bold text-gray-200 copy-text" id="reference">{{ $reference }}</span>
        </div>
        <button onclick="copyReference()" class="mt-2 w-full bg-dark-300 hover:bg-dark-400 text-white py-2 rounded-lg transition text-sm">
            📋 Copy Reference
        </button>
    </div>

    <!-- Instructions -->
    <div class="bg-blue-600/20 border-2 border-blue-500/30 rounded-xl p-6 mb-6">
        <h3 class="text-lg font-bold mb-3 text-blue-400">📋 Instructions:</h3>
        <ol class="space-y-2 text-sm text-gray-300">
            <li class="flex items-start gap-2">
                <span class="font-bold">1.</span>
                <span>Copy the account number above</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="font-bold">2.</span>
                <span>Transfer exactly <strong>₦{{ number_format($amount + $charges, 2) }}</strong> to the account</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="font-bold">3.</span>
                <span>Your wallet will be credited with <strong>₦{{ number_format($amount, 2) }}</strong> automatically</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="font-bold">4.</span>
                <span>Confirmation usually takes 1-5 minutes</span>
            </li>
        </ol>
    </div>

    <!-- Status Check Button -->
    <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-lg p-6 text-center">
        <p class="text-gray-400 mb-4">Already transferred?</p>
        <button onclick="checkPaymentStatus()" class="w-full bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white py-4 rounded-xl font-bold text-lg transition glow-button shadow-lg shadow-red-accent/40">
            ✅ I've Made the Transfer
        </button>
        <p class="text-xs text-gray-500 mt-3">Your wallet will be credited automatically when payment is confirmed</p>
        @if(isset($transaction) && $transaction->status === 'pending')
        <div id="auto-check-indicator" class="mt-3 flex items-center justify-center gap-2 text-xs text-green-400">
            <span class="animate-pulse">🔄</span>
            <span>Auto-checking every 10 seconds...</span>
        </div>
        @endif
    </div>

    @else
    <!-- Error State -->
    <div class="bg-red-600/20 border-2 border-red-accent rounded-xl p-6 text-center">
        <div class="text-5xl mb-4">⚠️</div>
        <h2 class="text-xl font-bold mb-2 text-red-accent">Payment Setup Failed</h2>
        <p class="text-gray-400 mb-4">Unable to generate payment details. Please try again.</p>
        <a href="{{ route('wallet.index') }}" class="inline-block bg-gradient-to-r from-red-accent to-yellow-accent text-white px-6 py-3 rounded-lg font-semibold transition">
            Back to Wallet
        </a>
    </div>
    @endif
</div>

@section('scripts')
<script>
let checkingStatus = false;

function copyAccountNumber() {
    const text = document.getElementById('account-number').textContent;
    navigator.clipboard.writeText(text).then(() => {
        showAlert('Account number copied to clipboard! 📋', 'success');
    });
}

function copyReference() {
    const text = document.getElementById('reference').textContent;
    navigator.clipboard.writeText(text).then(() => {
        showAlert('Reference copied to clipboard! 📋', 'success');
    });
}

function checkPaymentStatus() {
    if (checkingStatus) return;
    
    checkingStatus = true;
    const btn = event.target;
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Checking... ⏳';

    fetch('{{ route("payvibe.check-status") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            reference: '{{ $reference ?? "" }}'
        })
    })
    .then(response => response.json())
    .then(data => {
        checkingStatus = false;
        btn.disabled = false;
        btn.textContent = originalText;

        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => {
                window.location.href = '{{ route("wallet.index") }}';
            }, 2000);
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        checkingStatus = false;
        btn.disabled = false;
        btn.textContent = originalText;
        showAlert('Error checking status. Please try again.', 'error');
        console.error('Error:', error);
    });
}

// Auto-check payment status every 10 seconds when on this page
let autoCheckInterval = null;

document.addEventListener('DOMContentLoaded', function() {
    // Only auto-check if transaction is still pending
    @if(isset($transaction) && $transaction->status === 'pending')
    autoCheckInterval = setInterval(() => {
        // Don't auto-check if user is currently manually checking
        if (!checkingStatus) {
            checkPaymentStatusAuto();
        }
    }, 10000); // Check every 10 seconds
    
    // Clean up interval when leaving page
    window.addEventListener('beforeunload', () => {
        if (autoCheckInterval) {
            clearInterval(autoCheckInterval);
        }
    });
    @endif
});

// Silent auto-check (doesn't show button loading state)
function checkPaymentStatusAuto() {
    fetch('{{ route("payvibe.check-status") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            reference: '{{ $reference ?? "" }}'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.status === 'paid') {
            // Stop auto-checking and hide indicator
            if (autoCheckInterval) {
                clearInterval(autoCheckInterval);
                autoCheckInterval = null;
            }
            const indicator = document.getElementById('auto-check-indicator');
            if (indicator) {
                indicator.style.display = 'none';
            }
            
            // Show success message and redirect
            showAlert('🎉 Payment Confirmed! Your wallet has been credited!', 'success');
            setTimeout(() => {
                window.location.href = '{{ route("wallet.index") }}';
            }, 2000);
        }
    })
    .catch(error => {
        // Silently fail on auto-check errors
        console.error('Auto-check error:', error);
    });
}
</script>
@endsection

