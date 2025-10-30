<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $tickets = auth()->user()->tickets()
            ->with('order')
            ->latest()
            ->paginate(15);

        return view('tickets.index', compact('tickets'));
    }

    public function create()
    {
        $orders = auth()->user()->orders()
            ->where('status', 'completed')
            ->with('product')
            ->latest()
            ->get();

        return view('tickets.create', compact('orders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'nullable|exists:orders,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:support,replacement',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:5120|mimes:jpg,jpeg,png,pdf',
        ]);

        $ticket = Ticket::create([
            'user_id' => auth()->id(),
            'order_id' => $request->order_id,
            'subject' => $request->subject,
            'message' => $request->message,
            'type' => $request->type,
            'is_replacement_request' => $request->type === 'replacement',
            'priority' => $request->type === 'replacement' ? 'high' : 'medium',
        ]);

        // Handle attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('ticket-attachments', 'public');
                
                $ticket->attachments()->create([
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Ticket created successfully! We\'ll get back to you soon.',
            'redirect' => route('tickets.show', $ticket),
        ]);
    }

    public function show(Ticket $ticket)
    {
        if ($ticket->user_id !== auth()->id() && !auth()->user()->is_admin) {
            abort(403);
        }

        $ticket->load(['user', 'order', 'attachments', 'replies.user']);

        return view('tickets.show', compact('ticket'));
    }

    public function reply(Request $request, Ticket $ticket)
    {
        if ($ticket->user_id !== auth()->id() && !auth()->user()->is_admin) {
            abort(403);
        }

        $request->validate([
            'message' => 'required|string',
        ]);

        $ticket->replies()->create([
            'user_id' => auth()->id(),
            'is_admin' => auth()->user()->is_admin,
            'message' => $request->message,
        ]);

        // Update ticket status
        if ($ticket->status === 'closed') {
            $ticket->update(['status' => 'open']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reply sent successfully!',
        ]);
    }
}






