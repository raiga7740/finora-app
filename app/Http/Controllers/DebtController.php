<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Services\CurrencyRateService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DebtController extends Controller
{
    /**
     * Display a listing of debts and receivables with KPI summaries.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $debts = $user->debts()
            ->with(['payments.wallet'])
            ->get()
            ->sort(function (Debt $a, Debt $b) {
                if ($a->status === 'active' && $b->status !== 'active') {
                    return -1;
                }
                if ($a->status !== 'active' && $b->status === 'active') {
                    return 1;
                }
                if ($a->is_overdue && ! $b->is_overdue) {
                    return -1;
                }
                if (! $a->is_overdue && $b->is_overdue) {
                    return 1;
                }

                $aDue = $a->due_date ? $a->due_date->timestamp : PHP_INT_MAX;
                $bDue = $b->due_date ? $b->due_date->timestamp : PHP_INT_MAX;

                if ($aDue !== $bDue) {
                    return $aDue <=> $bDue;
                }

                return $b->id <=> $a->id;
            })
            ->values();

        $totalPayableRemaining = 0.0;
        $totalReceivableRemaining = 0.0;
        $activePayableCount = 0;
        $activeReceivableCount = 0;
        $overdueCount = 0;
        $settledCount = 0;

        foreach ($debts as $debt) {
            if ($debt->status === 'paid') {
                $settledCount++;
                continue;
            }

            if ($debt->status === 'active') {
                if ($debt->type === 'payable') {
                    $totalPayableRemaining += $debt->remaining_amount;
                    $activePayableCount++;
                } else {
                    $totalReceivableRemaining += $debt->remaining_amount;
                    $activeReceivableCount++;
                }

                if ($debt->is_overdue) {
                    $overdueCount++;
                }
            }
        }

        $netDebtPosition = $totalReceivableRemaining - $totalPayableRemaining;

        $wallets = $user->wallets()
            ->orderByDesc('is_primary')
            ->orderBy('name')
            ->get();

        return view('debts.index', compact(
            'debts',
            'wallets',
            'totalPayableRemaining',
            'totalReceivableRemaining',
            'netDebtPosition',
            'activePayableCount',
            'activeReceivableCount',
            'overdueCount',
            'settledCount'
        ));
    }

    /**
     * Store a newly created debt or receivable record.
     */
    public function store(Request $request, CurrencyRateService $currencyService): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'type' => ['required', 'in:payable,receivable'],
            'person_name' => ['required', 'string', 'max:255'],
            'person_contact' => ['nullable', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'total_amount' => ['required', 'numeric', 'min:0.01'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'wallet_id' => ['nullable', 'exists:wallets,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $totalAmount = (float) $validated['total_amount'];
        $paidAmount = min($totalAmount, (float) ($validated['paid_amount'] ?? 0));
        $status = $paidAmount >= $totalAmount ? 'paid' : 'active';
        $userCurrency = strtoupper($user->currency ?? 'IDR');

        $debt = $user->debts()->create([
            'type' => $validated['type'],
            'person_name' => $validated['person_name'],
            'person_contact' => $validated['person_contact'] ?? null,
            'title' => $validated['title'],
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'currency' => $userCurrency,
            'start_date' => $validated['start_date'] ?? Carbon::now()->toDateString(),
            'due_date' => $validated['due_date'] ?? null,
            'status' => $status,
            'notes' => $validated['notes'] ?? null,
        ]);

        if (! empty($validated['wallet_id'])) {
            $wallet = $user->wallets()->where('id', $validated['wallet_id'])->first();
            if ($wallet) {
                $txType = $debt->type === 'payable' ? 'income' : 'expense';
                $category = $user->categories()->where('type', $txType)->first();
                $walletCurrency = strtoupper($wallet->currency ?? $userCurrency);
                $rateToBase = $currencyService->getExchangeRate($walletCurrency, $userCurrency);
                $baseAmount = round($totalAmount * $rateToBase, 2);

                $txTitle = $debt->type === 'payable'
                    ? __('Loan Received') . ': ' . $debt->title . ' (' . $debt->person_name . ')'
                    : __('Loan Given') . ': ' . $debt->title . ' (' . $debt->person_name . ')';

                $user->transactions()->create([
                    'wallet_id' => $wallet->id,
                    'category_id' => $category?->id,
                    'amount' => $totalAmount,
                    'currency' => $walletCurrency,
                    'exchange_rate' => $rateToBase,
                    'base_amount' => $baseAmount,
                    'type' => $txType,
                    'payment_method' => 'transfer',
                    'transaction_date' => $debt->start_date ? $debt->start_date->toDateString() : Carbon::now()->toDateString(),
                    'title' => $txTitle,
                    'notes' => $debt->notes,
                ]);
            }
        }

        return redirect()->route('debts.index')->with('status', __('Debt record created successfully.'));
    }

    /**
     * Update an existing debt or receivable record.
     */
    public function update(Request $request, Debt $debt): RedirectResponse
    {
        abort_if($debt->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'type' => ['required', 'in:payable,receivable'],
            'person_name' => ['required', 'string', 'max:255'],
            'person_contact' => ['nullable', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'total_amount' => ['required', 'numeric', 'min:0.01'],
            'due_date' => ['nullable', 'date'],
            'status' => ['required', 'in:active,paid,cancelled'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $totalAmount = (float) $validated['total_amount'];
        $status = $validated['status'];
        $paidAmount = (float) $debt->paid_amount;

        if ($status === 'paid' && $paidAmount < $totalAmount) {
            $paidAmount = $totalAmount;
        } elseif ($paidAmount >= $totalAmount && $status === 'active') {
            $status = 'paid';
        }

        $debt->update([
            'type' => $validated['type'],
            'person_name' => $validated['person_name'],
            'person_contact' => $validated['person_contact'] ?? null,
            'title' => $validated['title'],
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'due_date' => $validated['due_date'] ?? null,
            'status' => $status,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('debts.index')->with('status', __('Debt record updated successfully.'));
    }

    /**
     * Delete a debt or receivable record.
     */
    public function destroy(Request $request, Debt $debt): RedirectResponse
    {
        abort_if($debt->user_id !== $request->user()->id, 403);

        $debt->delete();

        return redirect()->route('debts.index')->with('status', __('Debt record deleted successfully.'));
    }

    /**
     * Record a partial or full installment payment on a debt/receivable.
     */
    public function recordPayment(Request $request, Debt $debt, CurrencyRateService $currencyService): RedirectResponse
    {
        $user = $request->user();
        abort_if($debt->user_id !== $user->id, 403);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['nullable', 'date'],
            'wallet_id' => ['nullable', 'exists:wallets,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $payAmount = (float) $validated['amount'];
        $paymentDate = $validated['payment_date'] ?? Carbon::now()->toDateString();
        $transactionId = null;
        $walletId = null;

        if (! empty($validated['wallet_id'])) {
            $wallet = $user->wallets()->where('id', $validated['wallet_id'])->first();
            if ($wallet) {
                $walletId = $wallet->id;
                $txType = $debt->type === 'payable' ? 'expense' : 'income';
                $category = $user->categories()->where('type', $txType)->first();
                $userCurrency = strtoupper($user->currency ?? 'IDR');
                $walletCurrency = strtoupper($wallet->currency ?? $userCurrency);
                $rateToBase = $currencyService->getExchangeRate($walletCurrency, $userCurrency);
                $baseAmount = round($payAmount * $rateToBase, 2);

                $txTitle = $debt->type === 'payable'
                    ? __('Debt Repayment') . ': ' . $debt->title . ' (' . $debt->person_name . ')'
                    : __('Loan Collection') . ': ' . $debt->title . ' (' . $debt->person_name . ')';

                $tx = $user->transactions()->create([
                    'wallet_id' => $wallet->id,
                    'category_id' => $category?->id,
                    'amount' => $payAmount,
                    'currency' => $walletCurrency,
                    'exchange_rate' => $rateToBase,
                    'base_amount' => $baseAmount,
                    'type' => $txType,
                    'payment_method' => 'transfer',
                    'transaction_date' => $paymentDate,
                    'title' => $txTitle,
                    'notes' => $validated['notes'] ?? null,
                ]);

                $transactionId = $tx->id;
            }
        }

        $debt->payments()->create([
            'user_id' => $user->id,
            'wallet_id' => $walletId,
            'transaction_id' => $transactionId,
            'amount' => $payAmount,
            'payment_date' => $paymentDate,
            'notes' => $validated['notes'] ?? null,
        ]);

        $newPaidAmount = (float) $debt->paid_amount + $payAmount;
        $newStatus = $newPaidAmount >= (float) $debt->total_amount ? 'paid' : 'active';

        $debt->update([
            'paid_amount' => $newPaidAmount,
            'status' => $newStatus,
        ]);

        return redirect()->route('debts.index')->with('status', __('Installment payment recorded successfully.'));
    }

    /**
     * Save Split Bill participant shares as Receivable records in the Debts ledger.
     */
    public function storeSplitBill(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'due_date' => ['nullable', 'date'],
            'payment_info' => ['nullable', 'string', 'max:255'],
            'participants' => ['required', 'array', 'min:1'],
            'participants.*.name' => ['required', 'string', 'max:255'],
            'participants.*.contact' => ['nullable', 'string', 'max:100'],
            'participants.*.amount' => ['required', 'numeric', 'min:0.01'],
            'participants.*.items_note' => ['nullable', 'string', 'max:500'],
        ]);

        $userCurrency = strtoupper($user->currency ?? 'IDR');
        $today = Carbon::now()->toDateString();
        $createdCount = 0;

        foreach ($validated['participants'] as $participant) {
            $shareAmount = round((float) $participant['amount'], 2);
            if ($shareAmount <= 0) {
                continue;
            }

            $noteParts = [];
            if (! empty($participant['items_note'])) {
                $noteParts[] = $participant['items_note'];
            }
            if (! empty($validated['payment_info'])) {
                $noteParts[] = __('Transfer to') . ': ' . $validated['payment_info'];
            }

            $user->debts()->create([
                'type' => 'receivable',
                'person_name' => $participant['name'],
                'person_contact' => $participant['contact'] ?? null,
                'title' => 'Split Bill: ' . $validated['title'],
                'total_amount' => $shareAmount,
                'paid_amount' => 0,
                'currency' => $userCurrency,
                'start_date' => $today,
                'due_date' => $validated['due_date'] ?? null,
                'status' => 'active',
                'notes' => ! empty($noteParts) ? implode(' | ', $noteParts) : null,
            ]);

            $createdCount++;
        }

        return redirect()->route('debts.index')->with(
            'status',
            __('Split Bill saved! :count receivable records have been added to your ledger.', ['count' => $createdCount])
        );
    }
}

