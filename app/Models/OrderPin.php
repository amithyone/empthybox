<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class OrderPin extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'pin',
        'is_used',
        'used_at',
        'used_ip',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'used_at' => 'datetime',
    ];

    protected $hidden = [
        'pin',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function setPinAttribute($value)
    {
        $this->attributes['pin'] = Crypt::encryptString($value);
    }

    public function getPinAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function verifyPin($inputPin)
    {
        try {
            $decryptedPin = Crypt::decryptString($this->attributes['pin']);
            return hash_equals($decryptedPin, $inputPin);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function markAsUsed($ip = null)
    {
        $this->update([
            'is_used' => true,
            'used_at' => now(),
            'used_ip' => $ip ?? request()->ip(),
        ]);
    }

    public static function generatePin()
    {
        return str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }
}

