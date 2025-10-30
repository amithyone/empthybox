<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Setting;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next)
    {
        // Always allow login, register, logout
        if ($request->is('login') || $request->is('register') || $request->is('logout')) {
            return $next($request);
        }

        // Allow admin routes - let auth middleware handle authentication
        if ($request->is('admin/*')) {
            return $next($request);
        }

        // Allow authenticated admin users to bypass maintenance
        if (auth()->check() && auth()->user()->is_admin) {
            return $next($request);
        }

        // Check maintenance mode
        $maintenanceMode = Setting::get('maintenance_mode', false);
        
        if ($maintenanceMode) {
            return response()->view('maintenance', [
                'message' => Setting::get('maintenance_message', 'We are currently performing scheduled maintenance. Please check back soon.')
            ], 503);
        }

        return $next($request);
    }
}
