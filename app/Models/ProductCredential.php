<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class ProductCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'username',
        'password',
        'email',
        'authenticator_code',
        'authenticator_site',
        'additional_info',
        'is_sold',
        'sold_to_order_id',
    ];

    protected $casts = [
        'is_sold' => 'boolean',
        'additional_info' => 'array',
    ];

    protected $hidden = [
        'username',
        'password',
        'email',
        'authenticator_code',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'sold_to_order_id');
    }

    // Encryption methods for sensitive data
    public function setUsernameAttribute($value)
    {
        $this->attributes['username'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getUsernameAttribute($value)
    {
        try {
            return $value ? Crypt::decryptString($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getPasswordAttribute($value)
    {
        try {
            return $value ? Crypt::decryptString($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getEmailAttribute($value)
    {
        try {
            return $value ? Crypt::decryptString($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function setAuthenticatorCodeAttribute($value)
    {
        $this->attributes['authenticator_code'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getAuthenticatorCodeAttribute($value)
    {
        try {
            return $value ? Crypt::decryptString($value) : null;
        } catch (\Exception $e) {
            return $value;
        }
    }
}



