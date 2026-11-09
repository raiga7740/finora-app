<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\CurrencyRateService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CalculatorController extends Controller
{
    /**
     * Display the Smart Financial Calculator suite.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // Financial snapshot for natural calculator bindings (@saldo, @income, @expense, @debt)
        $totalBalance = (float) ($user->transactions()
            ->selectRaw("SUM(CASE WHEN type='income' THEN COALESCE(base_amount, amount) ELSE -COALESCE(base_amount, amount) END) as net")
            ->value('net') ?? 0);

        $monthlyIncome = (float) $user->transactions()
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum(DB::raw('COALESCE(base_amount, amount)'));

        $monthlyExpense = (float) $user->transactions()
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum(DB::raw('COALESCE(base_amount, amount)'));

        $totalDebt = (float) $user->debts()
            ->where('status', 'active')
            ->where('type', 'payable')
            ->sum(DB::raw('CASE WHEN total_amount > paid_amount THEN (total_amount - paid_amount) ELSE 0 END'));

        $wallets = $user->wallets()->orderBy('is_primary', 'desc')->orderBy('name')->get();
        $categories = $user->categories()->orderBy('name')->get();
        $currencySymbol = $user->currency_symbol ?? 'Rp';
        $userLocale = app()->getLocale() ?? 'id';

        return view('calculator.index', compact(
            'totalBalance',
            'monthlyIncome',
            'monthlyExpense',
            'totalDebt',
            'wallets',
            'categories',
            'currencySymbol',
            'userLocale'
        ));
    }

    /**
     * Convert an evaluated calculation line into a real Finora transaction.
     */
    public function quickTransaction(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'amount'           => ['required', 'numeric', 'min:0.01'],
            'type'             => ['required', 'in:income,expense'],
            'wallet_id'        => ['nullable', 'exists:wallets,id'],
            'category_id'      => ['nullable', 'exists:categories,id'],
            'transaction_date' => ['nullable', 'date'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ]);

        $validated['transaction_date'] = $validated['transaction_date'] ?? Carbon::now()->toDateString();

        if (empty($validated['wallet_id'])) {
            $primaryWallet = $user->wallets()->where('is_primary', true)->first();
            $validated['wallet_id'] = $primaryWallet?->id;
        }

        if (!empty($validated['wallet_id'])) {
            $w = $user->wallets()->find($validated['wallet_id']);
            if ($w) {
                $validated['payment_method'] = $w->name;
                $validated['currency'] = $w->currency ?? $user->currency ?? 'IDR';
            }
        }

        $txCurrency = $validated['currency'] ?? $user->currency ?? 'IDR';
        $validated['currency'] = $txCurrency;
        $validated['base_amount'] = app(CurrencyRateService::class)->convert(
            $validated['amount'],
            $txCurrency,
            $user->currency ?? 'IDR'
        );

        $transaction = $user->transactions()->create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dicatat ke Finora!',
                'transaction' => $transaction,
            ]);
        }

        return redirect()->route('calculator.index')
            ->with('status', 'Transaksi berhasil disimpan dari kalkulator.');
    }
}

