@extends('layouts.app')

@section('title', 'Support Tickets - BiggestLogs')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 py-6 md:py-8 pb-20 md:pb-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <h1 class="text-2xl md:text-3xl font-bold gradient-text">Support Tickets</h1>
        <a href="{{ route('tickets.create') }}" 
           class="bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white px-6 py-3 rounded-xl font-semibold transition glow-button relative shadow-lg shadow-red-accent/40">
            <span class="relative z-10">+ New Ticket</span>
        </a>
    </div>

    @if($tickets->count() > 0)
    <div class="space-y-4">
        @foreach($tickets as $ticket)
        <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-lg hover:shadow-xl hover:border-yellow-accent/50 transition p-4 md:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex-1">
                    <h3 class="text-lg md:text-xl font-bold mb-2 text-gray-200">{{ $ticket->subject }}</h3>
                    <p class="text-xs md:text-sm text-gray-400 mb-2">{{ \Illuminate\Support\Str::limit($ticket->message, 100) }}</p>
                    <div class="flex flex-wrap items-center gap-3 text-xs md:text-sm">
                        <span class="px-3 py-1 rounded-full border
                            {{ $ticket->status === 'open' ? 'bg-blue-500/20 text-blue-400 border-blue-500/30' : '' }}
                            {{ $ticket->status === 'resolved' ? 'bg-green-600/20 text-green-400 border-green-500/30' : '' }}
                            {{ $ticket->status === 'closed' ? 'bg-gray-600/20 text-gray-400 border-gray-500/30' : '' }}">
                            {{ ucfirst($ticket->status) }}
                        </span>
                        @if($ticket->is_replacement_request)
                        <span class="px-3 py-1 rounded-full bg-yellow-accent/20 text-yellow-accent border border-yellow-accent/30">🔁 Replacement</span>
                        @endif
                        <span class="text-gray-500">{{ $ticket->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
                <a href="{{ route('tickets.show', $ticket) }}" 
                   class="bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white px-4 py-2 rounded-lg font-medium transition text-sm md:text-base shadow-lg shadow-red-accent/30 self-start sm:self-auto">
                    View
                </a>
            </div>
        </div>
        @endforeach
    </div>

    @if($tickets->hasPages())
    <div class="mt-6">
        {{ $tickets->links() }}
    </div>
    @endif
    @else
    <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-lg p-8 md:p-12 text-center">
        <p class="text-gray-400 text-lg mb-4">No support tickets yet.</p>
        <a href="{{ route('tickets.create') }}" 
           class="inline-block bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white px-6 py-3 rounded-xl font-semibold transition glow-button relative shadow-lg shadow-red-accent/40">
            <span class="relative z-10">Create Ticket</span>
        </a>
    </div>
    @endif
</div>
@endsection
