<?php

use App\Models\User;

if (!function_exists('currency_symbol')) {
    /**
     * Get the active currency symbol for the current user or default to 'Rp'.
     */
    function currency_symbol(?User $user = null): string
    {
        $targetUser = $user ?? auth()->user();
        if ($targetUser && method_exists($targetUser, 'getCurrencySymbolAttribute')) {
            return $targetUser->currency_symbol;
        }

        return 'Rp';
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format given amount based on current user's currency preferences.
     */
    function format_currency(float|int|string|null $amount, bool $withSign = false, ?User $user = null): string
    {
        $targetUser = $user ?? auth()->user();
        if ($targetUser && method_exists($targetUser, 'formatCurrency')) {
            return $targetUser->formatCurrency($amount, $withSign);
        }

        $num = (float) ($amount ?? 0);
        $isNegative = $num < 0;
        $abs = abs($num);
        $formatted = 'Rp' . number_format($abs, 0, ',', '.');

        if ($isNegative) {
            return '-' . $formatted;
        }

        if ($withSign && $num > 0) {
            return '+' . $formatted;
        }

        return $formatted;
    }
}

if (!function_exists('format_user_date')) {
    /**
     * Format given date using current user's date format preference.
     */
    function format_user_date($date, ?User $user = null): string
    {
        $targetUser = $user ?? auth()->user();
        if ($targetUser && method_exists($targetUser, 'formatUserDate')) {
            return $targetUser->formatUserDate($date);
        }

        if (empty($date)) {
            return '—';
        }

        $carbon = $date instanceof \Carbon\Carbon ? $date : \Carbon\Carbon::parse($date);
        return $carbon->format('d M Y');
    }
}

