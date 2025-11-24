<?php

namespace App\Helpers;

class CurrencyHelper
{
    const USD_TO_NGN_RATE = 1600;
    const BASE_CURRENCY = 'USD';
    const DISPLAY_CURRENCY = 'NGN';

    /**
     * Convert USD to NGN
     */
    public static function toNGN($usdAmount)
    {
        return $usdAmount * self::USD_TO_NGN_RATE;
    }

    /**
     * Format currency with symbol
     */
    public static function format($amount, $showSymbol = true)
    {
        $ngnAmount = self::toNGN($amount);
        $formatted = number_format($ngnAmount, 2);
        
        if ($showSymbol) {
            return '₦' . $formatted;
        }
        
        return $formatted;
    }

    /**
     * Format currency without conversion (for display)
     */
    public static function formatNGN($ngnAmount, $showSymbol = true)
    {
        $formatted = number_format($ngnAmount, 2);
        
        if ($showSymbol) {
            return '₦' . $formatted;
        }
        
        return $formatted;
    }

    /**
     * Convert NGN back to USD
     */
    public static function toUSD($ngnAmount)
    {
        return $ngnAmount / self::USD_TO_NGN_RATE;
    }

    /**
     * Get currency symbol
     */
    public static function symbol()
    {
        return '₦';
    }

    /**
     * Get currency code
     */
    public static function code()
    {
        return 'NGN';
    }
}

