<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'total_deposited',
        'total_withdrawn',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'total_deposited' => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function deposit($amount, $description = null, $gateway = 'manual')
    {
        $this->increment('balance', $amount);
        $this->increment('total_deposited', $amount);

        $reference = 'WLT' . time() . rand(1000, 9999);
        
        // Create deposit record
        return \App\Models\Deposit::create([
            'user_id' => $this->user_id,
            'wallet_id' => $this->id,
            'amount' => $amount,
            'final_amount' => $amount,
            'gateway' => $gateway,
            'status' => 'completed',
            'reference' => $reference,
            'description' => $description ?? 'Wallet deposit',
            'completed_at' => now(),
        ]);
    }

    public function withdraw($amount, $description = null)
    {
        if ($this->balance < $amount) {
            throw new \Exception('Insufficient wallet balance');
        }

        $this->decrement('balance', $amount);
        $this->increment('total_withdrawn', $amount);

        return Transaction::create([
            'user_id' => $this->user_id,
            'wallet_id' => $this->id,
            'type' => 'withdrawal',
            'amount' => $amount,
            'status' => 'completed',
            'reference' => 'WLT' . time() . rand(1000, 9999),
            'description' => $description ?? 'Wallet withdrawal',
        ]);
    }
}






