<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'details',
        'is_sold',
    ];

    protected $casts = [
        'is_sold' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get formatted credentials from pipe-separated details
     * Format: username|password|authenticator_code|email|email_password|recovery_email
     */
    public function getFormattedCredentialsAttribute()
    {
        if (!$this->details) {
            return [];
        }

        // Split by pipe and remove \r\n characters
        $details = trim(str_replace(["\r", "\n", "\\r"], '', $this->details));
        $parts = explode('|', $details);
        
        // Define field names based on position
        $fields = [];
        if (isset($parts[0]) && trim($parts[0])) $fields['username'] = trim($parts[0]);
        if (isset($parts[1]) && trim($parts[1])) $fields['password'] = trim($parts[1]);
        if (isset($parts[2]) && trim($parts[2])) $fields['authenticator_code'] = trim($parts[2]);
        if (isset($parts[3]) && trim($parts[3])) $fields['email'] = trim($parts[3]);
        if (isset($parts[4]) && trim($parts[4])) $fields['email_password'] = trim($parts[4]);
        if (isset($parts[5]) && trim($parts[5])) {
            $fields['recovery_email'] = trim($parts[5]);
            
            // Extract recovery website from recovery email domain
            // Example: anything@smvmail.com -> smvmail.com
            if (filter_var($fields['recovery_email'], FILTER_VALIDATE_EMAIL)) {
                $emailParts = explode('@', $fields['recovery_email']);
                if (isset($emailParts[1])) {
                    $fields['recovery_website'] = $emailParts[1];
                }
            }
        }
        if (isset($parts[6]) && trim($parts[6])) $fields['additional'] = trim($parts[6]);
        
        return $fields;
    }
}


