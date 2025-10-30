<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider_order_id',
        'service_id',
        'service_name',
        'country_id',
        'country_name',
        'phone_number',
        'status',
        'sms_code',
        'sms_text',
        'sms_received_at',
        'expires_at',
    ];

    protected $casts = [
        'sms_received_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
