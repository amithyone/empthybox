<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankingDetail extends Model
{
    protected $fillable = [
        'bank_name',
        'account_name',
        'account_number',
        'account_type',
        'swift_code',
        'routing_number',
        'instructions',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFormattedAccountAttribute(): string
    {
        return $this->account_name . ' - ' . $this->account_number . ' (' . $this->bank_name . ')';
    }

    public function manualPayments()
    {
        return $this->hasMany(ManualPayment::class);
    }
}
