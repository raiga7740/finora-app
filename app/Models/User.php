<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'currency',
        'date_format',
        'ledger_mode',
        'locale',
        'timezone',
    ];

    /**
     * Get currency symbol for a given currency code.
     */
    public static function currencySymbol(?string $currency = 'IDR'): string
    {
        return match (strtoupper($currency ?? 'IDR')) {
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
     * Get currency symbol.
     */
    public function getCurrencySymbolAttribute(): string
    {
        return self::currencySymbol($this->currency);
    }

    /**
     * Format given amount with user's localized currency symbol and decimals.
     */
    public function formatCurrency(float|int|string|null $amount, bool $withSign = false): string
    {
        $num = (float) ($amount ?? 0);
        $curr = strtoupper($this->currency ?? 'IDR');
        $isNegative = $num < 0;
        $abs = abs($num);

        $formatted = match ($curr) {
            'USD', 'SGD', 'AUD', 'GBP' => $this->currency_symbol . number_format($abs, 2, '.', ','),
            'EUR' => $this->currency_symbol . number_format($abs, 2, ',', '.'),
            'JPY' => $this->currency_symbol . number_format($abs, 0, '', ','),
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
     * Format date using user's date format preference.
     */
    public function formatUserDate($date): string
    {
        if (empty($date)) return '—';
        $carbon = $date instanceof \Carbon\Carbon ? $date->copy() : \Carbon\Carbon::parse($date);
        if (!empty($this->timezone)) {
            try {
                $carbon->setTimezone($this->timezone);
            } catch (\Throwable) {}
        }
        return $carbon->format($this->date_format ?? 'd M Y');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * User's categories.
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * User's transactions.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * User's budgets.
     */
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    /**
     * User's savings goals.
     */
    public function savingsGoals(): HasMany
    {
        return $this->hasMany(SavingsGoal::class);
    }

    /**
     * User's recurring bills & subscriptions.
     */
    public function recurringBills(): HasMany
    {
        return $this->hasMany(RecurringBill::class);
    }

    /**
     * User's multi-currency & digital wallets.
     */
    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class);
    }

    /**
     * User's savings contributions.
     */
    public function savingsContributions(): HasMany
    {
        return $this->hasMany(SavingsContribution::class);
    }

    /**
     * User's debts and receivables.
     */
    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }

    /**
     * User's debt installment payments.
     */
    public function debtPayments(): HasMany
    {
        return $this->hasMany(DebtPayment::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
