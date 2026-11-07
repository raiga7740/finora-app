<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $now = Carbon::now();

        $period = $request->get('period', 'this_month');
        $selectedYear = (int) $request->get('year', $now->year);
        $selectedMonth = $request->has('month') ? (int) $request->get('month') : (int) $now->month;

        if ($request->has('month') && $request->has('year')) {
            $period = 'custom';
            $startDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $periodLabel = $startDate->format('F Y');
            $dateRangeLabel = $startDate->format('M 1') . ' - ' . $endDate->format('M d, Y');
        } elseif ($period === 'last_30_days') {
            $startDate = $now->copy()->subDays(30)->startOfDay();
            $endDate = $now->copy()->endOfDay();
            $periodLabel = 'Last 30 Days';
            $dateRangeLabel = $startDate->format('M d') . ' - ' . $endDate->format('M d, Y');
        } elseif ($period === 'quarter') {
            $startDate = $now->copy()->firstOfQuarter()->startOfDay();
            $endDate = $now->copy()->lastOfQuarter()->endOfDay();
            $periodLabel = 'Q' . ceil($now->month / 3) . ' ' . $now->year;
            $dateRangeLabel = $startDate->format('M d') . ' - ' . $endDate->format('M d, Y');
        } else {
            $period = 'this_month';
            $startDate = $now->copy()->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
            $periodLabel = 'This Month (' . $now->format('F') . ')';
            $dateRangeLabel = $startDate->format('M 1') . ' - ' . $endDate->format('M d, Y');
        }

        $startOfLastMonth = $startDate->copy()->subMonth()->startOfMonth();
        $endOfLastMonth   = $startDate->copy()->subMonth()->endOfMonth();

        // Income & Expense for the selected period (aggregated in user base currency)
        $totalIncome = (float) $user->transactions()
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum(DB::raw('COALESCE(base_amount, amount)'));

        $totalExpense = (float) $user->transactions()
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum(DB::raw('COALESCE(base_amount, amount)'));

        $netBalance = (float) ($user->transactions()
            ->selectRaw("SUM(CASE WHEN type='income' THEN COALESCE(base_amount, amount) ELSE -COALESCE(base_amount, amount) END) as net")
            ->value('net') ?? 0);

        $lastMonthNetBalance = (float) ($user->transactions()
            ->where('transaction_date', '<=', $endOfLastMonth)
            ->selectRaw("SUM(CASE WHEN type='income' THEN COALESCE(base_amount, amount) ELSE -COALESCE(base_amount, amount) END) as net")
            ->value('net') ?? 0);

        $netSavings = $totalIncome - $totalExpense;
        $savingsRate = $totalIncome > 0 ? round(($netSavings / $totalIncome) * 100, 1) : 0;
        $spendPercentage = $totalIncome > 0 ? round(($totalExpense / $totalIncome) * 100, 1) : 0;

        $incomeSourcesCount = $user->transactions()
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->count();

        $totalStatementsCount = $user->transactions()->count();

        // Last 6 months chart trajectory relative to selected period
        $referenceDate = $endDate->copy();
        $months = collect(range(5, 0))->map(function ($i) use ($referenceDate) {
            $m = $referenceDate->copy()->subMonths($i);
            return [
                'label' => $m->format('M Y'),
                'short' => $m->format('M'),
                'start' => $m->copy()->startOfMonth()->toDateString(),
                'end'   => $m->copy()->endOfMonth()->toDateString(),
            ];
        });

        $chartLabels  = $months->pluck('short');
        $chartIncome  = $months->map(fn ($m) => (float) $user->transactions()
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$m['start'], $m['end']])
            ->sum(DB::raw('COALESCE(base_amount, amount)')));
        $chartExpense = $months->map(fn ($m) => (float) $user->transactions()
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$m['start'], $m['end']])
            ->sum(DB::raw('COALESCE(base_amount, amount)')));

        // Spending by category for the period
        $spendingGroup = $user->transactions()
            ->with('category')
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->get()
            ->groupBy('category_id');

        $colors = ['#00c078', '#3b82f6', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4', '#ec4899'];
        $colorIndex = 0;

        $spendingByCategory = $spendingGroup->map(function ($items) use ($totalExpense, $colors, &$colorIndex) {
            $sum = (float) $items->sum(fn ($tx) => (float) ($tx->base_amount ?? $tx->amount));
            $pct = $totalExpense > 0 ? round(($sum / $totalExpense) * 100) : 0;
            $color = $colors[$colorIndex % count($colors)];
            $colorIndex++;
            return [
                'name'       => $items->first()->category->name ?? 'Uncategorized',
                'total'      => $sum,
                'percentage' => $pct,
                'color'      => $color,
            ];
        })->sortByDesc('total')->values();

        // Recent transactions (5) for the selected period (or latest overall if period is empty)
        $recentTransactions = $user->transactions()
            ->with('category')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date', 'desc')
            ->limit(5)
            ->get();

        if ($recentTransactions->isEmpty()) {
            $recentTransactions = $user->transactions()
                ->with('category')
                ->orderBy('transaction_date', 'desc')
                ->limit(5)
                ->get();
        }

        // Available past months for date selector dropdown
        $availableMonths = collect(range(0, 11))->map(function ($i) use ($now) {
            $m = $now->copy()->subMonths($i);
            return [
                'month' => $m->month,
                'year'  => $m->year,
                'label' => $m->format('F Y'),
                'range' => $m->startOfMonth()->format('M 1') . ' - ' . $m->endOfMonth()->format('M d, Y'),
            ];
        });

        // Budget health
        $budgets = $user->budgets()->with('category')->get();

        // Featured savings goal
        $featuredGoal = $user->savingsGoals()->orderBy('created_at', 'desc')->first();

        // Upcoming recurring bills
        $upcomingBills = $user->recurringBills()
            ->active()
            ->with('category')
            ->get()
            ->filter(fn ($b) => !$b->isPaidForCurrentCycle())
            ->sortBy(fn ($b) => $b->next_due_date->timestamp)
            ->take(3)
            ->values();

        return view('dashboard', compact(
            'period',
            'periodLabel',
            'dateRangeLabel',
            'selectedMonth',
            'selectedYear',
            'availableMonths',
            'startDate',
            'endDate',
            'totalIncome',
            'totalExpense',
            'netBalance',
            'lastMonthNetBalance',
            'netSavings',
            'savingsRate',
            'spendPercentage',
            'incomeSourcesCount',
            'totalStatementsCount',
            'chartLabels',
            'chartIncome',
            'chartExpense',
            'spendingByCategory',
            'recentTransactions',
            'budgets',
            'featuredGoal',
            'upcomingBills'
        ));
    }
}
