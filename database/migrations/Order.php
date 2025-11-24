<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'credential_id',
        'product_detail_id',
        'order_number',
        'amount',
        'status',
        'payment_method',
        'transaction_id',
        'has_replacement_request',
        'is_replaced',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'has_replacement_request' => 'boolean',
        'is_replaced' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (!$order->order_number) {
                $order->order_number = 'BL-' . strtoupper(uniqid());
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function credential()
    {
        return $this->belongsTo(ProductCredential::class);
    }

    public function productDetail()
    {
        return $this->belongsTo(ProductDetail::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function pin()
    {
        return $this->hasOne(OrderPin::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}






