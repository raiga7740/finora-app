<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinancialWrappedController extends Controller
{
    /**
     * Display the Monthly Financial Wrap-up (Spotify Wrapped style interactive story & infographic).
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $now = Carbon::now();

        $month = (int) $request->input('month', $now->month);
        $year = (int) $request->input('year', $now->year);

        $month = max(1, min(12, $month));
        $year = max(2020, min(2035, $year));

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        $prevStart = $startDate->copy()->subMonth()->startOfMonth();
        $prevEnd = $startDate->copy()->subMonth()->endOfMonth();

        // Fetch all transactions in selected month
        $transactions = $user->transactions()
            ->with(['category', 'wallet'])
            ->whereBetween('transaction_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderByDesc('amount')
            ->get();

        $expenses = $transactions->where('type', 'expense');
        $incomes = $transactions->where('type', 'income');

        $totalIncome = (float) $incomes->sum(fn ($t) => (float) ($t->base_amount ?? $t->amount));
        $totalExpense = (float) $expenses->sum(fn ($t) => (float) ($t->base_amount ?? $t->amount));
        $netSavings = $totalIncome - $totalExpense;
        $savingsRate = $totalIncome > 0 ? round(($netSavings / $totalIncome) * 100, 1) : 0.0;

        // Previous month expense for comparison
        $prevExpense = (float) $user->transactions()
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$prevStart->toDateString(), $prevEnd->toDateString()])
            ->get()
            ->sum(fn ($t) => (float) ($t->base_amount ?? $t->amount));

        $expenseDiffPct = $prevExpense > 0
            ? round((($totalExpense - $prevExpense) / $prevExpense) * 100, 1)
            : 0.0;

        // Top Spending Categories
        $categoryTotals = [];
        foreach ($expenses as $tx) {
            $catName = $tx->category?->name ?? __('Uncategorized');
            $amt = (float) ($tx->base_amount ?? $tx->amount);
            if (! isset($categoryTotals[$catName])) {
                $categoryTotals[$catName] = ['name' => $catName, 'amount' => 0.0, 'count' => 0];
            }
            $categoryTotals[$catName]['amount'] += $amt;
            $categoryTotals[$catName]['count']++;
        }

        usort($categoryTotals, fn ($a, $b) => $b['amount'] <=> $a['amount']);
        $topCategories = array_slice($categoryTotals, 0, 3);
        foreach ($topCategories as &$c) {
            $c['percentage'] = $totalExpense > 0 ? round(($c['amount'] / $totalExpense) * 100, 1) : 0.0;
        }
        unset($c);

        $topCategory = $topCategories[0] ?? [
            'name' => __('No Expenses Yet'),
            'amount' => 0.0,
            'count' => 0,
            'percentage' => 0.0,
        ];

        // Biggest Single Expense
        $biggestExpense = $expenses->sortByDesc(fn ($t) => (float) ($t->base_amount ?? $t->amount))->first();

        // Day of Week Spending Pattern & Zero-Spend Days
        $dayNames = [
            0 => __('Sunday'),
            1 => __('Monday'),
            2 => __('Tuesday'),
            3 => __('Wednesday'),
            4 => __('Thursday'),
            5 => __('Friday'),
            6 => __('Saturday'),
        ];
        $dayTotals = array_fill(0, 7, 0.0);
        $activeExpenseDates = [];

        foreach ($expenses as $tx) {
            $d = $tx->transaction_date instanceof Carbon ? $tx->transaction_date : Carbon::parse($tx->transaction_date);
            $dow = $d->dayOfWeek;
            $dayTotals[$dow] += (float) ($tx->base_amount ?? $tx->amount);
            $activeExpenseDates[$d->toDateString()] = true;
        }

        $peakDowIndex = 0;
        $peakDowAmount = 0.0;
        foreach ($dayTotals as $dow => $amt) {
            if ($amt > $peakDowAmount) {
                $peakDowAmount = $amt;
                $peakDowIndex = $dow;
            }
        }
        $peakDayName = $peakDowAmount > 0 ? $dayNames[$peakDowIndex] : '—';

        $elapsedDays = ($startDate->isCurrentMonth()) ? max(1, $now->day) : $daysInMonth;
        $zeroSpendDays = max(0, $elapsedDays - count($activeExpenseDates));
        $avgDailyExpense = $elapsedDays > 0 ? round($totalExpense / $elapsedDays, 2) : 0.0;

        // Favorite Wallet / Payment Method
        $methodCounts = [];
        foreach ($transactions as $tx) {
            $mName = $tx->wallet?->name ?: ($tx->payment_method ?: 'Cash');
            $methodCounts[$mName] = ($methodCounts[$mName] ?? 0) + 1;
        }
        arsort($methodCounts);
        $topMethod = ! empty($methodCounts) ? array_key_first($methodCounts) : '—';
        $topMethodCount = ! empty($methodCounts) ? reset($methodCounts) : 0;

        // Financial Persona Determination
        if ($transactions->isEmpty()) {
            $personaTitle = __('The Fresh Starter');
            $personaTagline = __('Ready to write your financial story this month.');
            $personaBadge = __('NEW CHAPTER');
        } elseif ($savingsRate >= 30) {
            $personaTitle = __('The Wealth Architect');
            $personaTagline = __('You saved over 30% of your income this month—disciplined, strategic, and unstoppable.');
            $personaBadge = __('TOP 5% SAVER');
        } elseif ($savingsRate >= 15) {
            $personaTitle = __('The Mindful Strategist');
            $personaTagline = __('You strike a healthy balance between enjoying life today and securing tomorrow.');
            $personaBadge = __('BALANCED PRO');
        } elseif ($topCategory['percentage'] >= 40 && preg_match('/(food|makan|dining)/i', $topCategory['name'])) {
            $personaTitle = __('The Culinary Explorer');
            $personaTagline = __('Good food is your top investment this month—just keep an eye on your dining budget cap!');
            $personaBadge = __('FOODIE MODE');
        } else {
            $personaTitle = __('The Dynamic Mover');
            $personaTagline = __('High cash-flow velocity this month. Setting a 20% auto-transfer to savings next month will boost your score.');
            $personaBadge = __('ACTIVE FLOW');
        }

        // Available months for selector (last 6 months)
        $availableMonths = [];
        for ($i = 0; $i < 6; $i++) {
            $mDate = $now->copy()->subMonths($i)->startOfMonth();
            $availableMonths[] = [
                'month' => $mDate->month,
                'year' => $mDate->year,
                'label' => $mDate->translatedFormat('F Y'),
            ];
        }

        $wrap = [
            'period_label' => $startDate->translatedFormat('F Y'),
            'month' => $month,
            'year' => $year,
            'total_transactions' => $transactions->count(),
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_savings' => $netSavings,
            'savings_rate' => $savingsRate,
            'expense_diff_pct' => $expenseDiffPct,
            'top_category' => $topCategory,
            'top_categories' => $topCategories,
            'biggest_expense' => $biggestExpense,
            'peak_day_name' => $peakDayName,
            'peak_day_amount' => $peakDowAmount,
            'zero_spend_days' => $zeroSpendDays,
            'elapsed_days' => $elapsedDays,
            'avg_daily_expense' => $avgDailyExpense,
            'top_method' => $topMethod,
            'top_method_count' => $topMethodCount,
            'persona_title' => $personaTitle,
            'persona_tagline' => $personaTagline,
            'persona_badge' => $personaBadge,
        ];

        return view('wrapped.index', compact('wrap', 'availableMonths'));
    }
}

