<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyRateService
{
    /**
     * Standard benchmark exchange rates relative to 1 IDR.
     * Value represents how many IDR are in 1 unit of the given currency.
     */
    public const RATES_TO_IDR = [
        'IDR' => 1.0,
        'USD' => 16000.0,
        'SGD' => 12000.0,
        'EUR' => 17200.0,
        'GBP' => 20500.0,
        'JPY' => 105.0,
        'AUD' => 10500.0,
    ];

    /**
     * Get the active rates map (with cached live API support and robust offline fallback).
     *
     * @return array<string, float>
     */
    public function getRatesToIdr(): array
    {
        return Cache::remember('finora_currency_rates_to_idr', now()->addHours(12), function () {
            return $this->fetchLiveRates() ?? self::RATES_TO_IDR;
        });
    }

    /**
     * Fetch live market rates via open exchangerate API with quick timeout.
     *
     * @return array<string, float>|null
     */
    protected function fetchLiveRates(): ?array
    {
        try {
            $resp = Http::withoutVerifying()
                ->timeout(3)
                ->get('https://open.er-api.com/v6/latest/USD');

            if ($resp->successful()) {
                $rates = $resp->json('rates');
                $idrRate = (float) ($rates['IDR'] ?? 0);

                if ($idrRate > 0) {
                    $result = ['IDR' => 1.0];
                    foreach (array_keys(self::RATES_TO_IDR) as $code) {
                        if ($code === 'IDR') {
                            continue;
                        }
                        $codePerUsd = (float) ($rates[$code] ?? 0);
                        if ($codePerUsd > 0) {
                            // 1 Unit of code in IDR = idrRate / codePerUsd
                            $result[$code] = round($idrRate / $codePerUsd, 2);
                        } else {
                            $result[$code] = self::RATES_TO_IDR[$code];
                        }
                    }

                    return $result;
                }
            }
        } catch (\Throwable $e) {
            Log::info('CurrencyRateService: Live rates unavailable, fallback to benchmark rates. ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Calculate exchange multiplier from one currency to another.
     */
    public function getExchangeRate(string $from, string $to): float
    {
        $from = strtoupper(trim($from));
        $to = strtoupper(trim($to));

        if ($from === $to) {
            return 1.0;
        }

        $rates = $this->getRatesToIdr();
        $fromIdr = $rates[$from] ?? (self::RATES_TO_IDR[$from] ?? 1.0);
        $toIdr = $rates[$to] ?? (self::RATES_TO_IDR[$to] ?? 1.0);

        if ($toIdr <= 0) {
            return 1.0;
        }

        return $fromIdr / $toIdr;
    }

    /**
     * Convert an amount from one currency to another with proper rounding.
     */
    public function convert(float|int|string|null $amount, string $from, string $to): float
    {
        $num = (float) ($amount ?? 0);
        $from = strtoupper(trim($from));
        $to = strtoupper(trim($to));

        if ($from === $to || $num == 0) {
            return $num;
        }

        $rate = $this->getExchangeRate($from, $to);
        $converted = $num * $rate;

        // IDR and JPY don't use decimal cents in daily transactions
        if ($to === 'IDR' || $to === 'JPY') {
            return round($converted, 0);
        }

        return round($converted, 2);
    }

    /**
     * Convert all user's ledger amounts from old currency to new currency.
     *
     * @return array{from: string, to: string, rate: float, count: int}
     */
    public function convertUserLedger(User $user, string $fromCurrency, string $toCurrency): array
    {
        $from = strtoupper(trim($fromCurrency));
        $to = strtoupper(trim($toCurrency));

        if ($from === $to) {
            return ['from' => $from, 'to' => $to, 'rate' => 1.0, 'count' => 0];
        }

        $rate = $this->getExchangeRate($from, $to);
        $count = 0;

        // 1. Wallets (initial_balance)
        foreach ($user->wallets()->get() as $wallet) {
            if (empty($wallet->currency) || strtoupper($wallet->currency) === $from) {
                $wallet->initial_balance = $this->convert($wallet->initial_balance, $from, $to);
                $wallet->currency = $to;
                $wallet->save();
                $count++;
            }
        }

        // 2. Transactions (amount & base_amount)
        foreach ($user->transactions()->get() as $tx) {
            if (empty($tx->currency) || strtoupper($tx->currency) === $from) {
                $tx->amount = $this->convert($tx->amount, $from, $to);
                $tx->currency = $to;
            }
            $tx->base_amount = $this->convert($tx->base_amount ?? $tx->amount, $from, $to);
            $tx->save();
            $count++;
        }

        // 3. Budgets (amount)
        foreach ($user->budgets()->get() as $budget) {
            $budget->amount = $this->convert($budget->amount, $from, $to);
            $budget->save();
            $count++;
        }

        // 4. Savings Goals (target_amount & current_amount)
        foreach ($user->savingsGoals()->get() as $goal) {
            $goal->target_amount = $this->convert($goal->target_amount, $from, $to);
            $goal->current_amount = $this->convert($goal->current_amount, $from, $to);
            $goal->save();
            $count++;
        }

        // 5. Savings Contributions (amount)
        foreach ($user->savingsContributions()->get() as $contrib) {
            $contrib->amount = $this->convert($contrib->amount, $from, $to);
            $contrib->save();
            $count++;
        }

        // 6. Recurring Bills (amount)
        foreach ($user->recurringBills()->get() as $bill) {
            $bill->amount = $this->convert($bill->amount, $from, $to);
            $bill->save();
            $count++;
        }

        // 7. Debts & Debt Payments
        if (\Illuminate\Support\Facades\Schema::hasTable('debts')) {
            foreach ($user->debts()->get() as $debt) {
                $debt->total_amount = $this->convert($debt->total_amount, $from, $to);
                $debt->paid_amount = $this->convert($debt->paid_amount, $from, $to);
                $debt->currency = $to;
                $debt->save();
                $count++;
            }
            foreach ($user->debtPayments()->get() as $payment) {
                $payment->amount = $this->convert($payment->amount, $from, $to);
                $payment->save();
                $count++;
            }
        }

        return [
            'from' => $from,
            'to' => $to,
            'rate' => $rate,
            'count' => $count,
        ];
    }

    /**
     * Get a human-readable exchange rate summary string (e.g., "1 USD = Rp16.000").
     */
    public function formatRateSummary(string $from, string $to): string
    {
        $rate = $this->getExchangeRate($from, $to);
        if ($from === 'IDR') {
            $invRate = $this->getExchangeRate($to, $from);
            return "1 {$to} ≈ Rp" . number_format($invRate, 0, ',', '.');
        }

        if ($to === 'IDR') {
            return "1 {$from} ≈ Rp" . number_format($rate, 0, ',', '.');
        }

        return "1 {$from} ≈ " . number_format($rate, 4, '.', ',') . " {$to}";
    }
}

