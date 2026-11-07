<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FinancialHealthService
{
    /**
     * Compute a comprehensive Financial Health Score (0-100), 5 Radar Pillars,
     * 50/30/20 rule breakdown, and personalized audit recommendations for a user.
     *
     * @return array<string, mixed>
     */
    public function analyze(User $user): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // 1. Monthly Income & Expense (Current Month, fallback to last 30 days if empty)
        $monthlyIncome = (float) $user->transactions()
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum(DB::raw('COALESCE(base_amount, amount)'));

        $monthlyExpense = (float) $user->transactions()
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum(DB::raw('COALESCE(base_amount, amount)'));

        if ($monthlyIncome == 0 && $monthlyExpense == 0) {
            $monthlyIncome = (float) $user->transactions()
                ->where('type', 'income')
                ->where('transaction_date', '>=', $now->copy()->subDays(30)->toDateString())
                ->sum(DB::raw('COALESCE(base_amount, amount)'));

            $monthlyExpense = (float) $user->transactions()
                ->where('type', 'expense')
                ->where('transaction_date', '>=', $now->copy()->subDays(30)->toDateString())
                ->sum(DB::raw('COALESCE(base_amount, amount)'));
        }

        // 2. Liquid Assets (Wallets + Savings Goals + Net Ledger Fallback)
        $currencyService = app(CurrencyRateService::class);
        $userCurrency = strtoupper($user->currency ?? 'IDR');

        $walletsTotal = 0.0;
        $wallets = $user->wallets()->get();
        foreach ($wallets as $wallet) {
            $walletsTotal += $currencyService->convert(
                $wallet->current_balance,
                $wallet->currency ?? $userCurrency,
                $userCurrency
            );
        }

        $savingsGoalsTotal = (float) $user->savingsGoals()->sum('current_amount');

        $ledgerNetBalance = (float) ($user->transactions()
            ->selectRaw("SUM(CASE WHEN type='income' THEN COALESCE(base_amount, amount) WHEN type='expense' THEN -COALESCE(base_amount, amount) ELSE 0 END) as net")
            ->value('net') ?? 0);

        $liquidAssets = max($walletsTotal, $ledgerNetBalance) + $savingsGoalsTotal;
        $liquidAssets = max(0.0, $liquidAssets);

        // 3. Debts & Receivables
        $totalPayable = 0.0;
        $totalReceivable = 0.0;
        $overdueDebtsCount = 0;

        if (Schema::hasTable('debts')) {
            $activeDebts = $user->debts()->where('status', 'active')->get();
            foreach ($activeDebts as $debt) {
                if ($debt->type === 'payable') {
                    $totalPayable += $debt->remaining_amount;
                } else {
                    $totalReceivable += $debt->remaining_amount;
                }
                if ($debt->is_overdue) {
                    $overdueDebtsCount++;
                }
            }
        }

        // 4. Recurring Bills Monthly Commitment
        $recurringMonthlyTotal = 0.0;
        $activeBills = $user->recurringBills()->where('status', 'active')->get();
        foreach ($activeBills as $bill) {
            $amt = (float) $bill->amount;
            if ($bill->billing_cycle === 'weekly') {
                $amt *= 4.33;
            } elseif ($bill->billing_cycle === 'yearly') {
                $amt /= 12;
            }
            $recurringMonthlyTotal += $amt;
        }

        // 5. Budgets Discipline
        $budgets = $user->budgets()->get();
        $totalBudgetsCount = $budgets->count();
        $overBudgetCount = 0;
        $totalBudgetLimit = 0.0;
        $totalBudgetSpent = 0.0;

        foreach ($budgets as $budget) {
            $limit = (float) $budget->amount;
            $totalBudgetLimit += $limit;

            $bStart = $budget->start_date ? Carbon::parse($budget->start_date) : $startOfMonth;
            $bEnd = $budget->end_date ? Carbon::parse($budget->end_date) : $endOfMonth;

            $q = $user->transactions()
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [$bStart, $bEnd]);

            if ($budget->category_id) {
                $q->where('category_id', $budget->category_id);
            }

            $spent = (float) $q->sum(DB::raw('COALESCE(base_amount, amount)'));
            $totalBudgetSpent += $spent;

            if ($limit > 0 && $spent > $limit) {
                $overBudgetCount++;
            }
        }

        // =====================================================================
        // PILLAR 1: Savings Rate (Weight 25%) — Benchmark: >= 20% of Income
        // =====================================================================
        $netMonthlySavings = $monthlyIncome - $monthlyExpense;
        if ($monthlyIncome > 0) {
            $savingsRatePct = round(($netMonthlySavings / $monthlyIncome) * 100, 1);
            if ($savingsRatePct >= 20) {
                $savingsScore = min(100, 85 + (int) round(($savingsRatePct - 20) * 0.75));
            } elseif ($savingsRatePct >= 0) {
                $savingsScore = (int) round(($savingsRatePct / 20) * 84);
            } else {
                $savingsScore = max(10, 40 + (int) round($savingsRatePct));
            }
        } elseif ($monthlyExpense > 0) {
            $savingsRatePct = -100.0;
            $savingsScore = 15;
        } else {
            $savingsRatePct = 0.0;
            $savingsScore = 50;
        }

        // =====================================================================
        // PILLAR 2: Emergency Runway (Weight 25%) — Benchmark: >= 3-6 Months
        // =====================================================================
        if ($monthlyExpense > 0) {
            $runwayMonths = round($liquidAssets / $monthlyExpense, 1);
            if ($runwayMonths >= 6) {
                $runwayScore = 100;
            } elseif ($runwayMonths >= 3) {
                $runwayScore = 75 + (int) round((($runwayMonths - 3) / 3) * 25);
            } else {
                $runwayScore = (int) round(($runwayMonths / 3) * 74);
            }
        } elseif ($liquidAssets > 0) {
            $runwayMonths = 6.0;
            $runwayScore = 90;
        } else {
            $runwayMonths = 0.0;
            $runwayScore = 50;
        }

        // =====================================================================
        // PILLAR 3: Debt Management (Weight 20%) — Benchmark: Debt Ratio <= 30%
        // =====================================================================
        $denominator = max($liquidAssets, $monthlyIncome, 1.0);
        if ($totalPayable <= 0) {
            $debtRatioPct = 0.0;
            $debtScore = 100;
        } else {
            $debtRatioPct = round(($totalPayable / $denominator) * 100, 1);
            if ($debtRatioPct <= 30) {
                $debtScore = 100 - (int) round(($debtRatioPct / 30) * 20);
            } elseif ($debtRatioPct <= 60) {
                $debtScore = 79 - (int) round((($debtRatioPct - 30) / 30) * 35);
            } else {
                $debtScore = max(15, 43 - (int) round(($debtRatioPct - 60) * 0.5));
            }

            if ($overdueDebtsCount > 0) {
                $debtScore = max(10, $debtScore - ($overdueDebtsCount * 15));
            }
        }

        // =====================================================================
        // PILLAR 4: Budget Discipline (Weight 15%) — Benchmark: 100% Within Cap
        // =====================================================================
        if ($totalBudgetsCount === 0) {
            $budgetUtilizationPct = 0.0;
            $budgetScore = 60; // Neutral prompt to set budgets
        } else {
            $budgetUtilizationPct = $totalBudgetLimit > 0
                ? round(($totalBudgetSpent / $totalBudgetLimit) * 100, 1)
                : 0.0;

            $compliantRatio = ($totalBudgetsCount - $overBudgetCount) / $totalBudgetsCount;
            $budgetScore = (int) round($compliantRatio * 100);

            if ($budgetUtilizationPct > 100) {
                $budgetScore = max(20, $budgetScore - 20);
            } elseif ($budgetUtilizationPct <= 85 && $overBudgetCount === 0) {
                $budgetScore = 100;
            }
        }

        // =====================================================================
        // PILLAR 5: Fixed Cost Load (Weight 15%) — Benchmark: Bills <= 30% Income
        // =====================================================================
        if ($recurringMonthlyTotal <= 0) {
            $fixedCostRatioPct = 0.0;
            $fixedCostScore = 90;
        } elseif ($monthlyIncome > 0) {
            $fixedCostRatioPct = round(($recurringMonthlyTotal / $monthlyIncome) * 100, 1);
            if ($fixedCostRatioPct <= 30) {
                $fixedCostScore = 100 - (int) round(($fixedCostRatioPct / 30) * 15);
            } elseif ($fixedCostRatioPct <= 50) {
                $fixedCostScore = 84 - (int) round((($fixedCostRatioPct - 30) / 20) * 30);
            } else {
                $fixedCostScore = max(20, 53 - (int) round(($fixedCostRatioPct - 50) * 0.6));
            }
        } else {
            $fixedCostRatioPct = 100.0;
            $fixedCostScore = 45;
        }

        // =====================================================================
        // OVERALL WEIGHTED SCORE (0 - 100)
        // =====================================================================
        $overallScore = (int) round(
            ($savingsScore * 0.25) +
            ($runwayScore * 0.25) +
            ($debtScore * 0.20) +
            ($budgetScore * 0.15) +
            ($fixedCostScore * 0.15)
        );
        $overallScore = max(0, min(100, $overallScore));

        if ($overallScore >= 85) {
            $tierKey = 'wealth_builder';
            $tierLabel = __('Wealth Builder');
            $tierColor = 'emerald';
            $tierSummary = __('Your financial foundation is exceptional. Cash flow, emergency reserves, and debt obligations are well within optimal CFP benchmarks.');
        } elseif ($overallScore >= 70) {
            $tierKey = 'healthy';
            $tierLabel = __('Healthy & Stable');
            $tierColor = 'teal';
            $tierSummary = __('Your finances are healthy and well-balanced. Fine-tuning a few pillars can accelerate your net worth growth.');
        } elseif ($overallScore >= 50) {
            $tierKey = 'fair';
            $tierLabel = __('Fair / Needs Attention');
            $tierColor = 'amber';
            $tierSummary = __('Your financial structure is functioning, but certain areas such as savings rate, runway, or debt load need proactive adjustment.');
        } else {
            $tierKey = 'vulnerable';
            $tierLabel = __('Vulnerable / High Priority');
            $tierColor = 'red';
            $tierSummary = __('Immediate attention is recommended to stabilize cash flow, build emergency liquidity, and reduce high-priority obligations.');
        }

        $pillars = [
            [
                'key' => 'savings',
                'name' => __('Savings Rate'),
                'score' => $savingsScore,
                'weight' => '25%',
                'metric_value' => $savingsRatePct . '%',
                'benchmark' => '≥ 20%',
                'status' => $savingsScore >= 80 ? __('Optimal') : ($savingsScore >= 55 ? __('Moderate') : __('Low')),
                'description' => __('Percentage of monthly income retained after expenses.'),
            ],
            [
                'key' => 'runway',
                'name' => __('Emergency Runway'),
                'score' => $runwayScore,
                'weight' => '25%',
                'metric_value' => $runwayMonths . ' ' . __('Months'),
                'benchmark' => '3–6 ' . __('Months'),
                'status' => $runwayScore >= 80 ? __('Optimal') : ($runwayScore >= 55 ? __('Moderate') : __('Low')),
                'description' => __('How many months your liquid cash can cover monthly expenses.'),
            ],
            [
                'key' => 'debt',
                'name' => __('Debt Immunity'),
                'score' => $debtScore,
                'weight' => '20%',
                'metric_value' => $debtRatioPct . '%',
                'benchmark' => '≤ 30%',
                'status' => $debtScore >= 80 ? __('Optimal') : ($debtScore >= 55 ? __('Moderate') : __('High Load')),
                'description' => __('Ratio of unpaid payable debt against liquid assets & income.'),
            ],
            [
                'key' => 'budget',
                'name' => __('Budget Discipline'),
                'score' => $budgetScore,
                'weight' => '15%',
                'metric_value' => $totalBudgetsCount > 0 ? (($totalBudgetsCount - $overBudgetCount) . '/' . $totalBudgetsCount . ' ' . __('OK')) : __('No Cap Set'),
                'benchmark' => '100% ' . __('OK'),
                'status' => $budgetScore >= 80 ? __('Optimal') : ($budgetScore >= 55 ? __('Moderate') : __('Over Cap')),
                'description' => __('Adherence to monthly category spending limits.'),
            ],
            [
                'key' => 'fixed_cost',
                'name' => __('Fixed Bill Load'),
                'score' => $fixedCostScore,
                'weight' => '15%',
                'metric_value' => $fixedCostRatioPct . '%',
                'benchmark' => '≤ 30%',
                'status' => $fixedCostScore >= 80 ? __('Optimal') : ($fixedCostScore >= 55 ? __('Moderate') : __('Heavy')),
                'description' => __('Share of monthly income locked into recurring bills & subscriptions.'),
            ],
        ];

        // =====================================================================
        // PERSONALIZED ACTIONABLE RECOMMENDATIONS
        // =====================================================================
        $recommendations = [];

        if ($savingsScore < 75) {
            $recommendations[] = [
                'priority' => 'high',
                'title' => __('Boost Monthly Savings Rate to 20%'),
                'detail' => __('Your current savings rate is :rate%. Try setting aside at least 20% of incoming salary immediately into a Savings Goal.', ['rate' => $savingsRatePct]),
                'action_label' => __('Open Savings Goals'),
                'action_url' => route('savings.index'),
            ];
        }

        if ($runwayScore < 75) {
            $recommendations[] = [
                'priority' => 'high',
                'title' => __('Build a 3-Month Emergency Reserve'),
                'detail' => __('Your liquid cash currently covers :months months of expenses. Aim for at least 3 to 6 months of living expenses in liquid accounts.', ['months' => $runwayMonths]),
                'action_label' => __('Manage Wallets & Accounts'),
                'action_url' => route('wallets.index'),
            ];
        }

        if ($debtScore < 80 || $overdueDebtsCount > 0) {
            $recommendations[] = [
                'priority' => 'high',
                'title' => __('Prioritize Settling Active / Overdue Debts'),
                'detail' => __('You have active payable obligations (:overdue overdue). Reducing debt improves your net worth and frees up monthly cash flow.', ['overdue' => $overdueDebtsCount]),
                'action_label' => __('Open Debts & Loans Tracker'),
                'action_url' => route('debts.index'),
            ];
        }

        if ($totalBudgetsCount === 0 || $overBudgetCount > 0) {
            $recommendations[] = [
                'priority' => 'medium',
                'title' => $totalBudgetsCount === 0
                    ? __('Set Monthly Category Budget Ceilings')
                    : __('Review Over-Budget Spending Categories'),
                'detail' => $totalBudgetsCount === 0
                    ? __('Creating monthly spending caps for dining, shopping, and entertainment prevents cash-flow leakage.')
                    : __(':count budget categories have exceeded their monthly ceiling.', ['count' => $overBudgetCount]),
                'action_label' => __('Configure Budgets'),
                'action_url' => route('budgets.index'),
            ];
        }

        if ($totalReceivable > 0) {
            $recommendations[] = [
                'priority' => 'medium',
                'title' => __('Collect Pending Receivables (Piutang)'),
                'detail' => __('You have :amount in active receivables owed to you. Use the 1-click WhatsApp reminder to speed up collection.', ['amount' => $user->formatCurrency($totalReceivable)]),
                'action_label' => __('View Receivables'),
                'action_url' => route('debts.index'),
            ];
        }

        if (empty($recommendations)) {
            $recommendations[] = [
                'priority' => 'low',
                'title' => __('Maintain Your Wealth Builder Momentum'),
                'detail' => __('All 5 financial pillars are in great shape. Consider allocating surplus cash into long-term investment wallets or higher savings targets.'),
                'action_label' => __('View Financial Reports'),
                'action_url' => route('reports.index'),
            ];
        }

        // =====================================================================
        // 50 / 30 / 20 RULE ALLOCATION SNAPSHOT
        // =====================================================================
        $needsAmount = $recurringMonthlyTotal + ($monthlyExpense * 0.55);
        if ($needsAmount > $monthlyExpense) {
            $needsAmount = $monthlyExpense * 0.65;
        }
        $wantsAmount = max(0.0, $monthlyExpense - $needsAmount);
        $savingsAmount = max(0.0, $netMonthlySavings);

        $needsPct = $monthlyIncome > 0 ? round(($needsAmount / $monthlyIncome) * 100, 1) : 0.0;
        $wantsPct = $monthlyIncome > 0 ? round(($wantsAmount / $monthlyIncome) * 100, 1) : 0.0;
        $savedPct = $monthlyIncome > 0 ? max(0.0, round(($savingsAmount / $monthlyIncome) * 100, 1)) : 0.0;

        return [
            'overall_score' => $overallScore,
            'tier_key' => $tierKey,
            'tier_label' => $tierLabel,
            'tier_color' => $tierColor,
            'tier_summary' => $tierSummary,
            'pillars' => $pillars,
            'recommendations' => $recommendations,
            'metrics' => [
                'monthly_income' => $monthlyIncome,
                'monthly_expense' => $monthlyExpense,
                'net_monthly_savings' => $netMonthlySavings,
                'liquid_assets' => $liquidAssets,
                'total_payable' => $totalPayable,
                'total_receivable' => $totalReceivable,
                'net_worth' => $liquidAssets + $totalReceivable - $totalPayable,
                'recurring_monthly' => $recurringMonthlyTotal,
                'runway_months' => $runwayMonths,
                'savings_rate_pct' => $savingsRatePct,
                'debt_ratio_pct' => $debtRatioPct,
            ],
            'rule_50_30_20' => [
                'needs_pct' => $needsPct,
                'wants_pct' => $wantsPct,
                'savings_pct' => $savedPct,
                'needs_amount' => $needsAmount,
                'wants_amount' => $wantsAmount,
                'savings_amount' => $savingsAmount,
            ],
        ];
    }
}

