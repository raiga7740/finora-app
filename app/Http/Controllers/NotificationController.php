<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;

class NotificationController extends Controller
{
    /**
     * Build dynamic financial notifications for the given user.
     */
    public static function getForUser(User $user): array
    {
        $notifications = [];
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $isEn = ($user->locale ?? 'en') === 'en';

        // 1. Check Monthly Cash Flow Deficit
        $monthlyIncome = (float) $user->transactions()
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $monthlyExpense = (float) $user->transactions()
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        if ($monthlyExpense > 0 && $monthlyExpense > $monthlyIncome) {
            $notifications[] = [
                'id' => 'cashflow_deficit_' . $now->format('Ym') . '_' . round($monthlyExpense),
                'type' => 'danger',
                'title' => $isEn ? 'Cash Flow Deficit Warning' : 'Peringatan Defisit Arus Kas',
                'message' => $isEn
                    ? 'Monthly spending (' . $user->formatCurrency($monthlyExpense) . ') exceeds total income (' . $user->formatCurrency($monthlyIncome) . ').'
                    : 'Pengeluaran bulan ini (' . $user->formatCurrency($monthlyExpense) . ') melampaui pemasukan (' . $user->formatCurrency($monthlyIncome) . ').',
                'url' => route('reports.index'),
                'time' => $isEn ? 'This Month' : 'Bulan Ini',
            ];
        }

        // 2. Check Budgets (Over budget, Near limit, or Active status)
        $budgets = $user->budgets()->with('category')->get();
        foreach ($budgets as $budget) {
            $spent = $budget->spent;
            $amount = (float) $budget->amount;
            $rawPct = $amount > 0 ? ($spent / $amount) * 100 : 0;
            $remaining = max(0, $amount - $spent);

            if ($rawPct >= 100) {
                $notifications[] = [
                    'id' => 'budget_over_' . $budget->id . '_' . round($spent),
                    'type' => 'danger',
                    'title' => ($isEn ? 'Over Budget: ' : 'Over Budget: ') . $budget->name,
                    'message' => $isEn
                        ? 'Spending reached ' . $user->formatCurrency($spent) . ' of ' . $user->formatCurrency($amount) . ' limit (' . round($rawPct) . '%).'
                        : 'Pengeluaran mencapai ' . $user->formatCurrency($spent) . ' dari batas ' . $user->formatCurrency($amount) . ' (' . round($rawPct) . '%).',
                    'url' => route('budgets.index'),
                    'time' => $isEn ? 'Limit Exceeded' : 'Melebihi Batas',
                ];
            } elseif ($rawPct >= 80) {
                $notifications[] = [
                    'id' => 'budget_warn_' . $budget->id . '_' . round($rawPct),
                    'type' => 'warning',
                    'title' => ($isEn ? 'Budget Near Limit: ' : 'Budget Hampir Habis: ') . $budget->name,
                    'message' => $isEn
                        ? 'Used ' . round($rawPct) . '% (' . $user->formatCurrency($remaining) . ' remaining).'
                        : 'Sudah terpakai ' . round($rawPct) . '% (Sisa anggaran ' . $user->formatCurrency($remaining) . ').',
                    'url' => route('budgets.index'),
                    'time' => $isEn ? 'Warning Limit' : 'Waspada Limit',
                ];
            } else {
                $notifications[] = [
                    'id' => 'budget_safe_' . $budget->id . '_' . round($rawPct),
                    'type' => 'info',
                    'title' => ($isEn ? 'Budget Status: ' : 'Status Budget: ') . $budget->name,
                    'message' => $isEn
                        ? 'Under control (' . round($rawPct) . '% used of ' . $user->formatCurrency($amount) . ' limit).'
                        : 'Terkendali (' . round($rawPct) . '% terpakai dari batas ' . $user->formatCurrency($amount) . ').',
                    'url' => route('budgets.index'),
                    'time' => $isEn ? 'Healthy' : 'Aman',
                ];
            }
        }

        // 3. Check Recurring Bills & Subscriptions
        $recurringBills = $user->recurringBills()->active()->get();
        foreach ($recurringBills as $bill) {
            if (!$bill->isPaidForCurrentCycle()) {
                if ($bill->isOverdue()) {
                    $notifications[] = [
                        'id' => 'bill_overdue_' . $bill->id . '_' . $bill->days_until_due,
                        'type' => 'danger',
                        'title' => ($isEn ? 'Overdue Bill: ' : 'Tagihan Terlambat: ') . $bill->name,
                        'message' => $isEn
                            ? 'Bill of ' . $user->formatCurrency($bill->amount) . ' is unpaid and past its due date.'
                            : 'Tagihan ' . $user->formatCurrency($bill->amount) . ' belum dibayar dan telah melewati tanggal jatuh tempo.',
                        'url' => route('recurring-bills.index'),
                        'time' => $isEn ? 'Overdue by ' . abs($bill->days_until_due) . ' Days' : 'Terlewat ' . abs($bill->days_until_due) . ' Hari',
                    ];
                } elseif ($bill->days_until_due <= 3) {
                    $daysText = $bill->days_until_due === 0
                        ? ($isEn ? 'Due Today' : 'Hari Ini')
                        : ($isEn ? $bill->days_until_due . ' Days Left' : $bill->days_until_due . ' Hari Lagi');
                    $notifications[] = [
                        'id' => 'bill_due_' . $bill->id . '_' . $bill->days_until_due,
                        'type' => 'warning',
                        'title' => ($isEn ? 'Bill Due: ' : 'Tagihan Jatuh Tempo: ') . $bill->name,
                        'message' => $isEn
                            ? 'Bill of ' . $user->formatCurrency($bill->amount) . ' is due ' . ($bill->days_until_due === 0 ? 'today' : 'in ' . $bill->days_until_due . ' days') . '.'
                            : 'Tagihan ' . $user->formatCurrency($bill->amount) . ' akan jatuh tempo ' . ($bill->days_until_due === 0 ? 'hari ini' : 'dalam ' . $bill->days_until_due . ' hari') . '.',
                        'url' => route('recurring-bills.index'),
                        'time' => $daysText,
                    ];
                }
            }
        }

        // 3b. Predictive cashflow / low balance alert (next 30 days)
        try {
            $forecast = app(\App\Services\CashflowForecastService::class)->forecast($user, 30);
            if (($forecast['has_data'] ?? false) && in_array($forecast['status'], ['critical', 'warning'], true)) {
                $critical = $forecast['status'] === 'critical';
                $notifications[] = [
                    'id' => 'cashflow_forecast_' . $forecast['status'] . '_' . ($forecast['zero_date'] ?? $forecast['lowest']['date']),
                    'type' => $critical ? 'danger' : 'warning',
                    'title' => $critical
                        ? ($isEn ? 'Balance Predicted to Run Out' : 'Saldo Diprediksi Habis')
                        : ($isEn ? 'Low Balance Ahead' : 'Saldo Menipis'),
                    'message' => $critical
                        ? ($isEn
                            ? 'At the current pace your balance hits zero in ' . $forecast['days_until_zero'] . ' days. Keep spending under ' . $user->formatCurrency($forecast['safe_daily']) . '/day.'
                            : 'Dengan pola saat ini saldo Anda habis dalam ' . $forecast['days_until_zero'] . ' hari. Batasi pengeluaran di bawah ' . $user->formatCurrency($forecast['safe_daily']) . '/hari.')
                        : ($isEn
                            ? 'Balance may drop to ' . $user->formatCurrency($forecast['lowest']['balance']) . ' within 30 days.'
                            : 'Saldo bisa turun hingga ' . $user->formatCurrency($forecast['lowest']['balance']) . ' dalam 30 hari.'),
                    'url' => route('cashflow.index'),
                    'time' => $isEn ? 'Forecast' : 'Prediksi',
                ];
            }
        } catch (\Throwable $e) {
            report($e);
        }

        // 4. Check Savings Goals Milestones & Deadlines
        $goals = $user->savingsGoals()->get();
        foreach ($goals as $goal) {
            $pct = $goal->progress_percentage;
            if ($pct >= 100) {
                $notifications[] = [
                    'id' => 'goal_done_' . $goal->id,
                    'type' => 'success',
                    'title' => ($isEn ? 'Savings Goal Reached: ' : 'Target Tabungan Tercapai: ') . $goal->name,
                    'message' => $isEn
                        ? 'Congratulations! Your savings reached 100% (' . $user->formatCurrency($goal->current_amount) . ').'
                        : 'Selamat! Tabungan Anda telah mencapai 100% (' . $user->formatCurrency($goal->current_amount) . ').',
                    'url' => route('savings.index'),
                    'time' => $isEn ? '100% Achieved' : 'Tercapai 100%',
                ];
            } elseif ($goal->target_date && $goal->target_date->isFuture() && $now->diffInDays($goal->target_date) <= 14) {
                $daysLeft = max(1, (int) round($now->diffInDays($goal->target_date)));
                $notifications[] = [
                    'id' => 'goal_deadline_' . $goal->id . '_' . $daysLeft,
                    'type' => 'warning',
                    'title' => ($isEn ? 'Savings Target Deadline: ' : 'Tenggat Tabungan: ') . $goal->name,
                    'message' => $isEn
                        ? $user->formatCurrency($goal->remaining) . ' remaining (' . $daysLeft . ' days left to goal).'
                        : 'Kurang ' . $user->formatCurrency($goal->remaining) . ' lagi (' . $daysLeft . ' hari menuju target).',
                    'url' => route('savings.index'),
                    'time' => $isEn ? $daysLeft . ' Days Left' : $daysLeft . ' Hari Lagi',
                ];
            } elseif ($pct >= 50) {
                $notifications[] = [
                    'id' => 'goal_half_' . $goal->id . '_' . round($pct),
                    'type' => 'success',
                    'title' => ($isEn ? 'Savings Milestone: ' : 'Milestone Tabungan: ') . $goal->name,
                    'message' => $isEn
                        ? 'Progress reached ' . round($pct) . '% (' . $user->formatCurrency($goal->current_amount) . ' saved).'
                        : 'Progres mencapai ' . round($pct) . '% (Terkumpul ' . $user->formatCurrency($goal->current_amount) . ').',
                    'url' => route('savings.index'),
                    'time' => $isEn ? 'Positive Progress' : 'Progres Positif',
                ];
            }
        }

        // 4. Latest Transaction Recorded
        $latestTx = $user->transactions()->with('category')->orderBy('transaction_date', 'desc')->orderBy('id', 'desc')->first();
        if ($latestTx) {
            $sign = $latestTx->type === 'income' ? '+' : '-';
            $notifications[] = [
                'id' => 'latest_tx_' . $latestTx->id,
                'type' => $latestTx->type === 'income' ? 'success' : 'info',
                'title' => ' Aktivitas Terakhir: ' . $latestTx->title,
                'message' => $sign . 'Rp' . number_format($latestTx->amount, 0, ',', '.') . ' pada kategori ' . ($latestTx->category->name ?? 'General') . '.',
                'url' => route('transactions.index'),
                'time' => $latestTx->transaction_date ? $latestTx->transaction_date->format('d M Y') : 'Baru saja',
            ];
        }

        // 5. Fallback welcome notification if empty
        if (empty($notifications)) {
            $notifications[] = [
                'id' => 'welcome_finora',
                'type' => 'info',
                'title' => 'Pusat Notifikasi Finora Aktif',
                'message' => 'Catat transaksi dan atur batas anggaran untuk menerima peringatan finansial otomatis.',
                'url' => route('transactions.create'),
                'time' => 'Real-Time Sync',
            ];
        }

        $items = array_slice($notifications, 0, 6);
        $hash = md5(json_encode(array_column($items, 'id')));
        $readHash = session('finora_notifications_read_hash');
        $unreadCount = ($readHash === $hash) ? 0 : count($items);

        return [
            'items' => $items,
            'hash' => $hash,
            'unread_count' => $unreadCount,
        ];
    }

    /**
     * Mark current notifications as read in user session.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = self::getForUser($user);

        session(['finora_notifications_read_hash' => $data['hash']]);

        return response()->json([
            'status' => 'ok',
            'unread_count' => 0,
        ]);
    }
}

