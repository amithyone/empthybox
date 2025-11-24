<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'type',
        'amount',
        'final_amount',
        'gateway',
        'reference',
        'status',
        'description',
        'gateway_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'gateway_response' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}






