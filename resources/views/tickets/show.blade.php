@extends('layouts.app')

@section('title', 'Ticket #' . $ticket->ticket_number . ' - BiggestLogs')

@section('content')
<div class="max-w-4xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 py-6 md:py-8 pb-20 md:pb-8">
    <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-2xl shadow-red-accent/10 p-4 md:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-gray-200">{{ $ticket->subject }}</h1>
                <p class="text-xs md:text-sm text-gray-400 mt-1">Ticket #{{ $ticket->ticket_number }}</p>
            </div>
            <span class="px-3 md:px-4 py-2 rounded-full border text-xs md:text-sm self-start sm:self-auto
                {{ $ticket->status === 'open' ? 'bg-blue-500/20 text-blue-400 border-blue-500/30' : '' }}
                {{ $ticket->status === 'resolved' ? 'bg-green-600/20 text-green-400 border-green-500/30' : '' }}
                {{ $ticket->status === 'closed' ? 'bg-gray-600/20 text-gray-400 border-gray-500/30' : '' }}">
                {{ ucfirst($ticket->status) }}
            </span>
        </div>

        <!-- Original Message -->
        <div class="border-l-4 border-yellow-accent pl-4 mb-6 bg-dark-300/30 rounded-r-lg p-4">
            <p class="font-semibold mb-2 text-gray-200">{{ $ticket->user->name }}</p>
            <p class="text-gray-300 whitespace-pre-line">{{ $ticket->message }}</p>
            <p class="text-xs text-gray-500 mt-2">{{ $ticket->created_at->format('M d, Y h:i A') }}</p>
        </div>

        <!-- Attachments -->
        @if($ticket->attachments->count() > 0)
        <div class="mb-6">
            <p class="font-semibold mb-3 text-gray-200">Attachments:</p>
            <div class="flex flex-wrap gap-2">
                @foreach($ticket->attachments as $attachment)
                <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank"
                   class="bg-dark-300 border border-dark-400 text-gray-300 px-4 py-2 rounded-lg hover:border-yellow-accent/50 transition">
                    📎 {{ $attachment->file_name }}
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Replies -->
        @if($ticket->replies->count() > 0)
        <div class="space-y-4 mb-6">
            <h3 class="font-semibold text-gray-200 mb-3">Replies</h3>
            @foreach($ticket->replies as $reply)
            <div class="border-l-4 {{ $reply->is_admin ? 'border-yellow-accent bg-dark-300/30' : 'border-dark-400 bg-dark-300/20' }} pl-4 p-4 rounded-r-lg">
                <p class="font-semibold mb-2 text-gray-200">
                    {{ $reply->user->name }}
                    @if($reply->is_admin)
                    <span class="text-xs bg-gradient-to-r from-red-accent to-yellow-accent text-white px-2 py-1 rounded ml-2">Admin</span>
                    @endif
                </p>
                <p class="text-gray-300 whitespace-pre-line">{{ $reply->message }}</p>
                <p class="text-xs text-gray-500 mt-2">{{ $reply->created_at->format('M d, Y h:i A') }}</p>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Reply Form -->
        @if($ticket->status !== 'closed')
        <div class="border-t border-dark-300 pt-6">
            <form id="reply-form">
                @csrf
                <textarea name="message" rows="4" required
                          class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 mb-4 placeholder-gray-500 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none resize-none"
                          placeholder="Type your reply..."></textarea>
                <button type="submit" 
                        class="bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white px-6 py-2 rounded-xl font-semibold transition glow-button relative shadow-lg shadow-red-accent/40">
                    <span class="relative z-10">Send Reply</span>
                </button>
            </form>
        </div>
        @endif

        <div class="mt-6">
            <a href="{{ route('tickets.index') }}" class="text-yellow-accent font-semibold hover:text-red-accent transition">← Back to Tickets</a>
        </div>
    </div>
</div>

@section('scripts')
<script>
document.getElementById('reply-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const btn = this.querySelector('button[type="submit"]');
    const btnText = btn.querySelector('span');
    btn.disabled = true;
    btnText.textContent = 'Sending...';
    
    try {
        const response = await fetch('{{ route("tickets.reply", $ticket) }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert(data.message, 'error');
            btn.disabled = false;
            btnText.textContent = 'Send Reply';
        }
    } catch (error) {
        showAlert('An error occurred. Please try again.', 'error');
        btn.disabled = false;
        btnText.textContent = 'Send Reply';
    }
});
</script>
@endsection
