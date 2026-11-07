<?php

namespace App\Http\Controllers;

use App\Models\RecurringBill;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    /**
     * Display the visual cash flow calendar.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $now = Carbon::now();
        $month = (int) $request->input('month', $now->month);
        $year = (int) $request->input('year', $now->year);

        // Bound month between 1 and 12
        if ($month < 1 || $month > 12) {
            $month = $now->month;
        }

        $currentDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $startOfMonth = $currentDate->copy()->startOfMonth();
        $endOfMonth = $currentDate->copy()->endOfMonth();

        // Calendar grid boundaries (Starts Monday, Ends Sunday)
        $calendarStart = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        // Fetch transactions in calendar window
        $transactions = $user->transactions()
            ->with(['category', 'wallet', 'destinationWallet'])
            ->whereBetween('transaction_date', [
                $calendarStart->toDateString(),
                $calendarEnd->toDateString()
            ])
            ->get();

        // Fetch active recurring bills
        $recurringBills = $user->recurringBills()
            ->active()
            ->get();

        // Group transactions by date string
        $txByDate = $transactions->groupBy(function ($item) {
            return $item->transaction_date ? $item->transaction_date->format('Y-m-d') : '';
        });

        // Build calendar days
        $period = CarbonPeriod::create($calendarStart, $calendarEnd);
        $days = [];

        $totalIncome = 0;
        $totalExpense = 0;
        $peakSpendAmount = 0;
        $peakSpendDate = null;
        $noSpendDaysCount = 0;

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $dayTxs = $txByDate->get($dateStr, collect());

            $income = (float) $dayTxs->where('type', 'income')->sum('amount');
            $expense = (float) $dayTxs->where('type', 'expense')->sum('amount');

            // Find bills due on this day (by day of month)
            $dayBills = $recurringBills->filter(function ($b) use ($date) {
                return (int) $b->due_day === (int) $date->day;
            })->values();

            $isCurrentMonth = $date->month === $month && $date->year === $year;
            $isToday = $date->isToday();
            $isPastOrToday = $date->isPast() || $isToday;

            if ($isCurrentMonth) {
                $totalIncome += $income;
                $totalExpense += $expense;

                if ($expense > $peakSpendAmount) {
                    $peakSpendAmount = $expense;
                    $peakSpendDate = $date->copy();
                }

                // Count no-spend days for days that have occurred
                if ($isPastOrToday && $expense == 0) {
                    $noSpendDaysCount++;
                }
            }

            $days[] = [
                'date'             => $date->copy(),
                'date_string'      => $dateStr,
                'day_number'       => $date->day,
                'is_current_month' => $isCurrentMonth,
                'is_today'         => $isToday,
                'is_future'        => $date->isFuture(),
                'income'           => $income,
                'expense'          => $expense,
                'net'              => $income - $expense,
                'transactions'     => $dayTxs->values(),
                'bills'            => $dayBills,
            ];
        }

        // Navigation links
        $prevMonthObj = $currentDate->copy()->subMonth();
        $nextMonthObj = $currentDate->copy()->addMonth();

        $categories = $user->categories()->orderBy('name')->get();
        $wallets = $user->wallets()->orderBy('is_primary', 'desc')->get();

        return view('calendar.index', [
            'days'             => $days,
            'currentMonth'     => $currentDate,
            'month'            => $month,
            'year'             => $year,
            'prevMonth'        => $prevMonthObj->month,
            'prevYear'         => $prevMonthObj->year,
            'nextMonth'        => $nextMonthObj->month,
            'nextYear'         => $nextMonthObj->year,
            'totalIncome'      => $totalIncome,
            'totalExpense'     => $totalExpense,
            'netSavings'       => $totalIncome - $totalExpense,
            'peakSpendAmount'  => $peakSpendAmount,
            'peakSpendDate'    => $peakSpendDate,
            'noSpendDaysCount' => $noSpendDaysCount,
            'categories'       => $categories,
            'wallets'          => $wallets,
        ]);
    }
}
