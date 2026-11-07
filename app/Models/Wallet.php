<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'currency',
        'account_number',
        'initial_balance',
        'color',
        'is_primary',
        'notes',
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'is_primary' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Calculate live balance of this wallet.
     */
    public function getCurrentBalanceAttribute(): float
    {
        $initial = (float) $this->initial_balance;

        $income = (float) $this->user->transactions()
            ->where('wallet_id', $this->id)
            ->where('type', 'income')
            ->sum('amount');

        $expense = (float) $this->user->transactions()
            ->where('wallet_id', $this->id)
            ->where('type', 'expense')
            ->sum('amount');

        $transfersIn = (float) $this->user->transactions()
            ->where('destination_wallet_id', $this->id)
            ->where('type', 'transfer')
            ->get()
            ->sum(function ($tx) {
                return (float) ($tx->destination_amount ?? $tx->amount);
            });

        $transfersOut = (float) $this->user->transactions()
            ->where('wallet_id', $this->id)
            ->where('type', 'transfer')
            ->sum('amount');

        return $initial + $income - $expense + $transfersIn - $transfersOut;
    }

    /**
     * Get the wallet's currency symbol.
     */
    public function getCurrencySymbolAttribute(): string
    {
        return match (strtoupper($this->currency ?? 'IDR')) {
            'USD' => '$',
            'SGD' => 'S$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'AUD' => 'A$',
            default => 'Rp',
        };
    }

    /**
     * Format given amount in this wallet's native currency.
     */
    public function formatBalance(?float $amount = null, bool $withSign = false): string
    {
        $num = (float) ($amount ?? $this->current_balance);
        $curr = strtoupper($this->currency ?? 'IDR');
        $sym = $this->currency_symbol;
        $isNegative = $num < 0;
        $abs = abs($num);

        $formatted = match ($curr) {
            'USD', 'SGD', 'AUD', 'GBP' => $sym . number_format($abs, 2, '.', ','),
            'EUR' => $sym . number_format($abs, 2, ',', '.'),
            'JPY' => $sym . number_format($abs, 0, '', ','),
            default => 'Rp' . number_format($abs, 0, ',', '.'),
        };

        if ($isNegative) {
            return '-' . $formatted;
        }

        if ($withSign && $num > 0) {
            return '+' . $formatted;
        }

        return $formatted;
    }

    /**
     * Get the wallet balance converted into the user's primary/base currency.
     */
    public function getConvertedBalanceAttribute(): float
    {
        $userCurrency = strtoupper($this->user->currency ?? 'IDR');
        $walletCurrency = strtoupper($this->currency ?? $userCurrency);

        if ($walletCurrency === $userCurrency) {
            return (float) $this->current_balance;
        }

        return app(\App\Services\CurrencyRateService::class)->convert(
            $this->current_balance,
            $walletCurrency,
            $userCurrency
        );
    }

    /**
     * Auto-seed default wallets for a user if none exist.
     */
    public static function seedDefaultWalletsForUser(User $user): void
    {
        if ($user->wallets()->exists()) {
            return;
        }

        $currency = strtoupper($user->currency ?? 'IDR');
        $isEnglish = ($user->locale ?? 'en') === 'en';

        $defaults = match ($currency) {
            'USD' => [
                [
                    'name' => 'Chase Premier Checking',
                    'type' => 'bank',
                    'account_number' => '•••• 4128',
                    'initial_balance' => 1500.00,
                    'color' => '#1170cf', // Chase Blue
                    'is_primary' => true,
                    'notes' => 'Primary checking & salary direct deposit',
                ],
                [
                    'name' => 'Bank of America Savings',
                    'type' => 'bank',
                    'account_number' => '•••• 8820',
                    'initial_balance' => 500.00,
                    'color' => '#e31837', // BofA Red
                    'is_primary' => false,
                    'notes' => 'Secondary savings reserve',
                ],
                [
                    'name' => 'PayPal & Apple Cash',
                    'type' => 'ewallet',
                    'account_number' => 'user@domain.com',
                    'initial_balance' => 150.00,
                    'color' => '#0079c1', // PayPal Blue
                    'is_primary' => false,
                    'notes' => 'Online checkout & contactless payments',
                ],
                [
                    'name' => 'Cash (Physical Wallet)',
                    'type' => 'cash',
                    'account_number' => null,
                    'initial_balance' => 0.00,
                    'color' => '#00c078', // Finora Emerald
                    'is_primary' => false,
                    'notes' => 'Physical wallet bills & coins',
                ],
            ],
            'EUR' => [
                [
                    'name' => 'Revolut Current Account',
                    'type' => 'bank',
                    'account_number' => 'LT•• •••• 4912',
                    'initial_balance' => 1200.00,
                    'color' => '#0075eb', // Revolut Blue
                    'is_primary' => true,
                    'notes' => 'Primary SEPA daily account',
                ],
                [
                    'name' => 'N26 Smart Savings',
                    'type' => 'bank',
                    'account_number' => 'DE•• •••• 1042',
                    'initial_balance' => 400.00,
                    'color' => '#36a18b', // N26 Teal
                    'is_primary' => false,
                    'notes' => 'Emergency savings sub-account',
                ],
                [
                    'name' => 'PayPal EU',
                    'type' => 'ewallet',
                    'account_number' => 'user@domain.com',
                    'initial_balance' => 100.00,
                    'color' => '#0079c1',
                    'is_primary' => false,
                    'notes' => 'Online payments & e-commerce',
                ],
                [
                    'name' => 'Cash (Euros)',
                    'type' => 'cash',
                    'account_number' => null,
                    'initial_balance' => 0.00,
                    'color' => '#00c078',
                    'is_primary' => false,
                    'notes' => 'Physical cash on hand',
                ],
            ],
            'GBP' => [
                [
                    'name' => 'Barclays Current Account',
                    'type' => 'bank',
                    'account_number' => '20-00-00 ••••1234',
                    'initial_balance' => 1000.00,
                    'color' => '#00aeef', // Barclays Light Blue
                    'is_primary' => true,
                    'notes' => 'Main current account & standing orders',
                ],
                [
                    'name' => 'Monzo Savings Pot',
                    'type' => 'bank',
                    'account_number' => '04-00-04 ••••5678',
                    'initial_balance' => 350.00,
                    'color' => '#ff3366', // Monzo Coral
                    'is_primary' => false,
                    'notes' => 'Everyday spending & bill pot',
                ],
                [
                    'name' => 'Wise Multi-Currency',
                    'type' => 'ewallet',
                    'account_number' => 'user@domain.com',
                    'initial_balance' => 100.00,
                    'color' => '#2ed06e', // Wise Green
                    'is_primary' => false,
                    'notes' => 'International transfers & card',
                ],
                [
                    'name' => 'Cash (Pounds)',
                    'type' => 'cash',
                    'account_number' => null,
                    'initial_balance' => 0.00,
                    'color' => '#00c078',
                    'is_primary' => false,
                    'notes' => 'Physical cash reserve',
                ],
            ],
            'SGD' => [
                [
                    'name' => 'DBS Multi-Currency Account',
                    'type' => 'bank',
                    'account_number' => '•••-•••••-1',
                    'initial_balance' => 1500.00,
                    'color' => '#d90000', // DBS Red
                    'is_primary' => true,
                    'notes' => 'Primary checking & salary',
                ],
                [
                    'name' => 'OCBC 360 Account',
                    'type' => 'bank',
                    'account_number' => '•••-••••••-001',
                    'initial_balance' => 500.00,
                    'color' => '#ed1c24', // OCBC Red
                    'is_primary' => false,
                    'notes' => 'Savings & interest bonuses',
                ],
                [
                    'name' => 'GrabPay & PayNow',
                    'type' => 'ewallet',
                    'account_number' => '+65-••••-9821',
                    'initial_balance' => 100.00,
                    'color' => '#00b140', // Grab Green
                    'is_primary' => false,
                    'notes' => 'Daily QR payments, transit & food',
                ],
                [
                    'name' => 'Cash (SGD)',
                    'type' => 'cash',
                    'account_number' => null,
                    'initial_balance' => 0.00,
                    'color' => '#00c078',
                    'is_primary' => false,
                    'notes' => 'Physical cash on hand',
                ],
            ],
            'AUD' => [
                [
                    'name' => 'Commonwealth Bank Smart Access',
                    'type' => 'bank',
                    'account_number' => '062-000 ••••1234',
                    'initial_balance' => 1500.00,
                    'color' => '#ffcc00', // CommBank Yellow
                    'is_primary' => true,
                    'notes' => 'Primary transaction account',
                ],
                [
                    'name' => 'ANZ Plus Savings',
                    'type' => 'bank',
                    'account_number' => '012-000 ••••5678',
                    'initial_balance' => 500.00,
                    'color' => '#007db8', // ANZ Blue
                    'is_primary' => false,
                    'notes' => 'High-interest savings',
                ],
                [
                    'name' => 'PayPal Australia',
                    'type' => 'ewallet',
                    'account_number' => 'user@domain.com',
                    'initial_balance' => 100.00,
                    'color' => '#0079c1',
                    'is_primary' => false,
                    'notes' => 'Online checkout & payments',
                ],
                [
                    'name' => 'Cash (AUD)',
                    'type' => 'cash',
                    'account_number' => null,
                    'initial_balance' => 0.00,
                    'color' => '#00c078',
                    'is_primary' => false,
                    'notes' => 'Physical cash on hand',
                ],
            ],
            'JPY' => [
                [
                    'name' => 'MUFG Bank (三菱UFJ銀行)',
                    'type' => 'bank',
                    'account_number' => '•••-••••123',
                    'initial_balance' => 150000,
                    'color' => '#ed1a3d', // MUFG Red
                    'is_primary' => true,
                    'notes' => 'Main salary & everyday checking',
                ],
                [
                    'name' => 'Japan Post Bank (ゆうちょ銀行)',
                    'type' => 'bank',
                    'account_number' => '•••••-••••1234',
                    'initial_balance' => 50000,
                    'color' => '#009944', // JP Post Green
                    'is_primary' => false,
                    'notes' => 'Secondary savings account',
                ],
                [
                    'name' => 'PayPay & Suica',
                    'type' => 'ewallet',
                    'account_number' => '090-••••-1234',
                    'initial_balance' => 15000,
                    'color' => '#ff0033', // PayPay Red
                    'is_primary' => false,
                    'notes' => 'QR code & IC transit wallet',
                ],
                [
                    'name' => 'Cash (JPY - 現金)',
                    'type' => 'cash',
                    'account_number' => null,
                    'initial_balance' => 0,
                    'color' => '#00c078',
                    'is_primary' => false,
                    'notes' => 'Physical cash & coins',
                ],
            ],
            default => [
                [
                    'name' => 'Bank BCA',
                    'type' => 'bank',
                    'account_number' => '•••• 8821',
                    'initial_balance' => 2500000,
                    'color' => '#005baa', // BCA Blue
                    'is_primary' => true,
                    'notes' => $isEnglish ? 'Primary operational account' : 'Rekening operasional utama',
                ],
                [
                    'name' => 'Bank Mandiri',
                    'type' => 'bank',
                    'account_number' => '•••• 1402',
                    'initial_balance' => 0,
                    'color' => '#003366', // Mandiri Dark Blue
                    'is_primary' => false,
                    'notes' => $isEnglish ? 'Secondary savings account' : 'Rekening tabungan sekunder',
                ],
                [
                    'name' => 'GoPay',
                    'type' => 'ewallet',
                    'account_number' => '0812-••••-9901',
                    'initial_balance' => 240000,
                    'color' => '#00aed6', // GoPay Cyan
                    'is_primary' => false,
                    'notes' => $isEnglish ? 'Daily digital wallet & QRIS' : 'Dompet digital harian & QRIS',
                ],
                [
                    'name' => $isEnglish ? 'Physical Cash' : 'Dompet Tunai (Cash)',
                    'type' => 'cash',
                    'account_number' => null,
                    'initial_balance' => 0,
                    'color' => '#00c078', // Finora Green
                    'is_primary' => false,
                    'notes' => $isEnglish ? 'Physical cash in wallet' : 'Uang tunai fisik di dompet',
                ],
            ],
        };

        foreach ($defaults as $wallet) {
            $wallet['currency'] = $wallet['currency'] ?? $currency;
            $created = $user->wallets()->create($wallet);

            // Backfill existing transactions if matching payment_method
            $slug = strtolower(explode(' ', $wallet['name'])[0]);
            if ($slug === 'bank') {
                $slug = strtolower(explode(' ', $wallet['name'])[1] ?? 'bank');
            }

            $user->transactions()
                ->whereNull('wallet_id')
                ->where('payment_method', $slug)
                ->update(['wallet_id' => $created->id]);
        }

        // If any transaction still has no wallet_id, attach to primary wallet
        $primary = $user->wallets()->where('is_primary', true)->first();
        if ($primary) {
            $user->transactions()
                ->whereNull('wallet_id')
                ->update(['wallet_id' => $primary->id]);
        }
    }
}

