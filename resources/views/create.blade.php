@extends('layouts.app')

@section('title', 'Create Ticket - BiggestLogs')

@section('content')
<div class="max-w-2xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 py-6 md:py-8 pb-20 md:pb-8">
    <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-2xl shadow-red-accent/10 p-4 md:p-6">
        <div class="flex items-center justify-between mb-4 md:mb-6">
            <h1 class="text-2xl md:text-3xl font-bold gradient-text">Create Support Ticket</h1>
            <a href="{{ route('tickets.index') }}" class="text-yellow-accent hover:text-red-accent transition flex items-center gap-2">
                <span>← Back</span>
            </a>
        </div>

        <form id="ticket-form" enctype="multipart/form-data">
            @csrf
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-300">Ticket Type</label>
                    <select name="type" id="ticket-type" required
                            class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none">
                        <option value="support" class="bg-dark-300">General Support</option>
                        <option value="replacement" class="bg-dark-300">Replacement Request</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-300">Related Order <span class="text-xs text-gray-500">(Optional)</span></label>
                    <select name="order_id"
                            class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none">
                        <option value="" class="bg-dark-300">Select an order...</option>
                        @foreach($orders as $order)
                        <option value="{{ $order->id }}" class="bg-dark-300">Order #{{ $order->order_number }} - {{ $order->product->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-300">Subject</label>
                    <input type="text" name="subject" required
                           class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-300">Message</label>
                    <textarea name="message" rows="6" required
                              class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none resize-none"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-300">Attachments <span class="text-xs text-gray-500">(Optional, Max 5 files)</span></label>
                    <input type="file" name="attachments[]" multiple accept="image/*,.pdf"
                           class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-red-accent file:text-white hover:file:bg-red-dark">
                    <p class="text-xs text-gray-500 mt-1">Max 5MB per file</p>
                </div>

                <button type="submit" 
                        class="w-full bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white py-3 rounded-xl font-semibold transition glow-button relative shadow-lg shadow-red-accent/40">
                    <span class="relative z-10">Create Ticket</span>
                </button>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
document.getElementById('ticket-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const btn = this.querySelector('button[type="submit"]');
    const btnText = btn.querySelector('span');
    btn.disabled = true;
    btnText.textContent = 'Creating...';
    
    try {
        const response = await fetch('{{ route("tickets.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1500);
        } else {
            showAlert(data.message, 'error');
            btn.disabled = false;
            btnText.textContent = 'Create Ticket';
        }
    } catch (error) {
        showAlert('An error occurred. Please try again.', 'error');
        btn.disabled = false;
        btnText.textContent = 'Create Ticket';
    }
});
</script>
@endsection
