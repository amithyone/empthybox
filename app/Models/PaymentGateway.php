<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'display_name',
        'description',
        'icon',
        'is_active',
        'is_enabled',
        'sort_order',
        'config',
        'supported_currencies',
        'min_amount',
        'max_amount',
        'fee_percentage',
        'fee_fixed',
        'instructions',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_enabled' => 'boolean',
        'config' => 'array',
        'supported_currencies' => 'array',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'fee_percentage' => 'decimal:2',
        'fee_fixed' => 'decimal:2',
    ];

    /**
     * Get active gateways ordered by sort_order
     */
    public static function getActive()
    {
        return self::where('is_active', true)
            ->where('is_enabled', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Check if gateway is available for a given amount
     */
    public function isAvailableForAmount($amount)
    {
        if ($this->min_amount && $amount < $this->min_amount) {
            return false;
        }
        if ($this->max_amount && $amount > $this->max_amount) {
            return false;
        }
        return true;
    }

    /**
     * Calculate fee for an amount
     */
    public function calculateFee($amount)
    {
        $percentageFee = ($amount * $this->fee_percentage) / 100;
        return $percentageFee + $this->fee_fixed;
    }
}
