<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        // Payment webhooks
        'paystack/webhook',
        'stripe/webhook',
        'razorpay/webhook',
    ];
}



