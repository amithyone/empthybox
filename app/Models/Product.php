<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'login_steps',
        'access_tips',
        'additional_instructions',
        'price',
        'stock_quantity',
        'preview_info',
        'account_type',
        'region',
        'flag',
        'is_verified',
        'is_active',
        'featured_image',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'preview_info' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function credentials()
    {
        return $this->hasMany(ProductCredential::class);
    }

    public function availableCredentials()
    {
        return $this->hasMany(ProductCredential::class)->where('is_sold', false);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function details()
    {
        return $this->hasMany(ProductDetail::class);
    }

    public function getAvailableStockAttribute()
    {
        // Count unsold details as stock if present
        try {
            return $this->details()->where('is_sold', false)->count();
        } catch (\Throwable $e) {
            return $this->attributes['available_stock'] ?? 0;
        }
    }
}


