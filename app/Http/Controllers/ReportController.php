<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $year = (int) $request->get('year', date('Y'));
        $month = $request->get('month', date('m'));

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $transactions = $user->transactions()
            ->with('category')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date', 'desc')
            ->get();

        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');
        $netSavings = $totalIncome - $totalExpense;
        $savingsRate = $totalIncome > 0 ? round(($netSavings / $totalIncome) * 100, 1) : 0;

        // Breakdown by Category
        $categoryBreakdown = $transactions
            ->where('type', 'expense')
            ->groupBy('category_id')
            ->map(function ($items) use ($totalExpense) {
                $sum = $items->sum('amount');
                $pct = $totalExpense > 0 ? round(($sum / $totalExpense) * 100, 1) : 0;
                return [
                    'name' => $items->first()->category->name ?? 'Uncategorized',
                    'total' => $sum,
                    'percentage' => $pct,
                ];
            })
            ->sortByDesc('total')
            ->values();

        // 12-month summary for chart
        $monthlyTrends = collect(range(11, 0))->map(function ($i) use ($user) {
            $date = Carbon::now()->subMonths($i);
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->endOfMonth();

            $inc = (float) $user->transactions()
                ->where('type', 'income')
                ->whereBetween('transaction_date', [$start, $end])
                ->sum('amount');

            $exp = (float) $user->transactions()
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [$start, $end])
                ->sum('amount');

            return [
                'label' => $date->format('M y'),
                'income' => $inc,
                'expense' => $exp,
                'savings' => $inc - $exp,
            ];
        });

        return view('reports.index', compact(
            'year',
            'month',
            'startDate',
            'endDate',
            'transactions',
            'totalIncome',
            'totalExpense',
            'netSavings',
            'savingsRate',
            'categoryBreakdown',
            'monthlyTrends'
        ));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $user = $request->user();
        $year = (int) $request->get('year', date('Y'));
        $month = $request->get('month', date('m'));

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $transactions = $user->transactions()
            ->with('category')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date', 'asc')
            ->get();

        $filename = "finora_report_{$year}_{$month}.csv";

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Date', 'Title', 'Category', 'Type', 'Payment Method', 'Amount (IDR)', 'Notes']);

            foreach ($transactions as $t) {
                fputcsv($handle, [
                    $t->id,
                    $t->transaction_date ? $t->transaction_date->format('Y-m-d') : '',
                    $t->title,
                    $t->category->name ?? 'Uncategorized',
                    ucfirst($t->type),
                    $t->payment_method ?? '',
                    $t->amount,
                    $t->notes ?? '',
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportPdf(Request $request): View
    {
        $user = $request->user();
        $year = (int) $request->get('year', date('Y'));
        $month = $request->get('month', date('m'));

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $transactions = $user->transactions()
            ->with('category')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date', 'desc')
            ->get();

        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');
        $netSavings = $totalIncome - $totalExpense;
        $savingsRate = $totalIncome > 0 ? round(($netSavings / $totalIncome) * 100, 1) : 0;

        return view('reports.pdf', compact(
            'user',
            'year',
            'month',
            'startDate',
            'endDate',
            'transactions',
            'totalIncome',
            'totalExpense',
            'netSavings',
            'savingsRate'
        ));
    }
}
