<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        $wallet = $user->wallet;
        
        $orders = $user->orders()
            ->with(['product', 'pin'])
            ->latest()
            ->limit(5)
            ->get();

        $recentTickets = $user->tickets()
            ->latest()
            ->limit(5)
            ->get();

        $stats = [
            'total_orders' => $user->orders()->count(),
            'wallet_balance' => $wallet ? $wallet->balance : 0,
            'pending_orders' => $user->orders()->where('status', 'pending')->count(),
            'open_tickets' => $user->tickets()->where('status', 'open')->count(),
        ];

        return view('dashboard.index', compact('user', 'wallet', 'orders', 'recentTickets', 'stats'));
    }
}






