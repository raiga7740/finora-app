<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CashflowForecastService
{
    public const ALLOWED_HORIZONS = [30, 60, 90];

    /**
     * Project the user's liquid balance day-by-day for the next $horizon days.
     *
     * The model combines: current liquid balance, average variable spending (excluding
     * recurring bill payments, which are projected explicitly), expected recurring bills,
     * debts payable due in the window, and a recurring income estimate.
     *
     * @return array<string, mixed>
     */
    public function forecast(User $user, int $horizon = 30): array
    {
        $horizon = in_array($horizon, self::ALLOWED_HORIZONS, true) ? $horizon : 30;
        $today = Carbon::today();
        $end = $today->copy()->addDays($horizon);
        $currency = strtoupper($user->currency ?? 'IDR');
        $fx = app(CurrencyRateService::class);

        // 1. Starting liquid balance (investment wallets are not liquid).
        $startBalance = 0.0;
        $walletCount = 0;
        foreach ($user->wallets()->get() as $wallet) {
            if ($wallet->type === 'investment') {
                continue;
            }
            $walletCount++;
            $startBalance += $fx->convert($wallet->current_balance, $wallet->currency ?? $currency, $currency);
        }

        $hasHistory = $user->transactions()->where('type', 'expense')->exists();
        if ($walletCount === 0 || ! $hasHistory) {
            return ['has_data' => false, 'horizon' => $horizon, 'wallet_count' => $walletCount];
        }

        // 2. Average daily variable spending over the last 60 days (bill payments excluded).
        $lookbackStart = $today->copy()->subDays(60);
        $firstTx = $user->transactions()->min('transaction_date');
        $observedDays = $firstTx
            ? (int) min(60, max(7, Carbon::parse($firstTx)->diffInDays($today) + 1))
            : 60;

        $variableSpend = (float) $user->transactions()
            ->where('type', 'expense')
            ->where('transaction_date', '>=', $lookbackStart->toDateString())
            ->where('title', 'not like', 'Pembayaran:%')
            ->sum(DB::raw('COALESCE(base_amount, amount)'));
        $dailySpend = $variableSpend / $observedDays;

        // 3. Event list (bills, debts, income).
        $events = [];

        foreach ($user->recurringBills()->active()->get() as $bill) {
            foreach ($this->billOccurrences($bill, $today, $end) as $date) {
                $events[] = [
                    'date' => $date,
                    'label' => $bill->name,
                    'kind' => 'bill',
                    'amount' => -(float) $bill->amount,
                ];
            }
        }

        $potentialReceivables = 0.0;
        foreach ($user->debts()->where('status', 'active')->get() as $debt) {
            $remaining = $fx->convert($debt->remaining_amount, $debt->currency ?? $currency, $currency);
            if ($remaining <= 0) {
                continue;
            }
            if ($debt->type === 'receivable') {
                $potentialReceivables += $remaining;

                continue;
            }
            if ($debt->due_date && $debt->due_date->lte($end)) {
                $events[] = [
                    'date' => $debt->due_date->lt($today) ? $today->copy() : $debt->due_date->copy()->startOfDay(),
                    'label' => $debt->title.' ('.$debt->person_name.')',
                    'kind' => 'debt',
                    'amount' => -$remaining,
                ];
            }
        }

        $income = $this->estimateIncome($user, $today);
        if ($income['monthly'] > 0) {
            $cursor = $today->copy()->startOfMonth();
            while ($cursor->lte($end)) {
                $date = $cursor->copy()->day(min($income['payday'], $cursor->daysInMonth));
                if ($date->gte($today) && $date->lte($end)) {
                    $events[] = [
                        'date' => $date,
                        'label' => __('Estimated income'),
                        'kind' => 'income',
                        'amount' => $income['monthly'],
                    ];
                }
                $cursor->addMonthNoOverflow();
            }
        }

        usort($events, fn ($a, $b) => $a['date']->timestamp <=> $b['date']->timestamp);

        // 4. Day-by-day simulation.
        $series = [['date' => $today->toDateString(), 'balance' => round($startBalance, 2)]];
        $balance = $startBalance;
        $lowest = ['date' => $today->toDateString(), 'balance' => $balance];
        $zeroDate = $balance < 0 ? $today->toDateString() : null;

        $byDay = [];
        foreach ($events as $event) {
            $byDay[$event['date']->toDateString()][] = $event;
        }

        for ($i = 1; $i <= $horizon; $i++) {
            $day = $today->copy()->addDays($i);
            $key = $day->toDateString();
            $balance -= $dailySpend;
            foreach ($byDay[$key] ?? [] as $event) {
                $balance += $event['amount'];
            }
            $series[] = ['date' => $key, 'balance' => round($balance, 2)];
            if ($balance < $lowest['balance']) {
                $lowest = ['date' => $key, 'balance' => $balance];
            }
            if ($zeroDate === null && $balance < 0) {
                $zeroDate = $key;
            }
        }

        // 5. Status & safe daily spend.
        $buffer = $dailySpend * 7;
        $status = $zeroDate !== null ? 'critical' : ($lowest['balance'] < $buffer ? 'warning' : 'safe');

        $fixedOut = abs(array_sum(array_map(fn ($e) => $e['amount'] < 0 ? $e['amount'] : 0, $events)));
        $incomeIn = array_sum(array_map(fn ($e) => $e['amount'] > 0 ? $e['amount'] : 0, $events));
        $safeDaily = max(0.0, ($startBalance + $incomeIn - $fixedOut) / $horizon);

        $upcoming = array_map(fn ($e) => [
            'date' => $e['date']->toDateString(),
            'label' => $e['label'],
            'kind' => $e['kind'],
            'amount' => round($e['amount'], 2),
        ], $events);

        return [
            'has_data' => true,
            'horizon' => $horizon,
            'status' => $status,
            'start_balance' => round($startBalance, 2),
            'end_balance' => round($balance, 2),
            'lowest' => ['date' => $lowest['date'], 'balance' => round($lowest['balance'], 2)],
            'zero_date' => $zeroDate,
            'days_until_zero' => $zeroDate ? (int) $today->diffInDays(Carbon::parse($zeroDate)) : null,
            'daily_spend' => round($dailySpend, 2),
            'safe_daily' => round($safeDaily, 2),
            'buffer' => round($buffer, 2),
            'expected_income' => round($incomeIn, 2),
            'fixed_outflow' => round($fixedOut, 2),
            'potential_receivables' => round($potentialReceivables, 2),
            'income_monthly' => round($income['monthly'], 2),
            'series' => $series,
            'events' => $upcoming,
            'wallet_count' => $walletCount,
        ];
    }

    /**
     * Expected dates a recurring bill will be paid inside [$from, $to].
     *
     * @return array<int, Carbon>
     */
    protected function billOccurrences($bill, Carbon $from, Carbon $to): array
    {
        $dates = [];

        if ($bill->billing_cycle === 'monthly') {
            $day = min(max(1, (int) $bill->due_day), 28);
            $cursor = $from->copy()->startOfMonth();
            while ($cursor->lte($to)) {
                $date = $cursor->copy()->day($day)->startOfDay();
                $isCurrentMonth = $cursor->isSameMonth($from);
                if ($isCurrentMonth && $bill->isPaidForCurrentCycle()) {
                    // already paid this cycle
                } elseif ($date->lt($from)) {
                    if ($isCurrentMonth) {
                        $dates[] = $from->copy(); // overdue: assume it is paid now
                    }
                } elseif ($date->lte($to)) {
                    $dates[] = $date;
                }
                $cursor->addMonthNoOverflow();
            }
        } elseif ($bill->billing_cycle === 'weekly') {
            $date = $bill->next_due_date->copy()->startOfDay();
            if ($date->lt($from)) {
                $date = $from->copy();
            }
            while ($date->lte($to)) {
                $dates[] = $date->copy();
                $date->addDays(7);
            }
        } else { // yearly
            $date = $bill->next_due_date->copy()->startOfDay();
            if ($date->gte($from) && $date->lte($to)) {
                $dates[] = $date;
            }
        }

        return $dates;
    }

    /**
     * Estimate recurring monthly income and the typical payday from the last 90 days.
     *
     * @return array{monthly: float, payday: int}
     */
    protected function estimateIncome(User $user, Carbon $today): array
    {
        $rows = $user->transactions()
            ->where('type', 'income')
            ->where('transaction_date', '>=', $today->copy()->subDays(90)->toDateString())
            ->get(['transaction_date', 'amount', 'base_amount']);

        if ($rows->isEmpty()) {
            return ['monthly' => 0.0, 'payday' => 1];
        }

        $total = (float) $rows->sum(fn ($r) => (float) ($r->base_amount ?? $r->amount));
        $firstTx = $user->transactions()->min('transaction_date');
        $months = $firstTx
            ? min(3, max(1, Carbon::parse($firstTx)->diffInDays($today) / 30))
            : 1;

        $days = $rows->map(fn ($r) => (int) Carbon::parse($r->transaction_date)->day)->sort()->values();
        $payday = min(28, (int) $days[(int) floor(($days->count() - 1) / 2)]);

        return ['monthly' => $total / $months, 'payday' => max(1, $payday)];
    }
}

