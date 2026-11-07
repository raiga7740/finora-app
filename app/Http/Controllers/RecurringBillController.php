<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\RecurringBill;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecurringBillController extends Controller
{
    /**
     * Display a listing of recurring bills & subscriptions.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $bills = $user->recurringBills()
            ->with('category')
            ->get();

        // Sort bills: active unpaid first (closest due date), then paid, then paused
        $bills = $bills->sort(function ($a, $b) {
            $aPaid = $a->isPaidForCurrentCycle();
            $bPaid = $b->isPaidForCurrentCycle();

            if ($a->status === 'active' && $b->status !== 'active') return -1;
            if ($a->status !== 'active' && $b->status === 'active') return 1;

            if (!$aPaid && $bPaid) return -1;
            if ($aPaid && !$bPaid) return 1;

            return $a->next_due_date->timestamp <=> $b->next_due_date->timestamp;
        })->values();

        // Calculate summary metrics
        $totalMonthlyCommitment = 0;
        $totalActive = 0;
        $paidCount = 0;
        $unpaidCount = 0;
        $totalPaidAmount = 0;
        $totalUpcomingAmount = 0;

        foreach ($bills as $bill) {
            if ($bill->status === 'active') {
                $totalActive++;

                // Normalize to monthly equivalent
                $monthlyAmount = (float) $bill->amount;
                if ($bill->billing_cycle === 'weekly') {
                    $monthlyAmount = $bill->amount * 4.33;
                } elseif ($bill->billing_cycle === 'yearly') {
                    $monthlyAmount = $bill->amount / 12;
                }
                $totalMonthlyCommitment += $monthlyAmount;

                if ($bill->isPaidForCurrentCycle()) {
                    $paidCount++;
                    $totalPaidAmount += (float) $bill->amount;
                } else {
                    $unpaidCount++;
                    $totalUpcomingAmount += (float) $bill->amount;
                }
            }
        }

        $categories = $user->categories()
            ->whereIn('type', ['expense'])
            ->orderBy('name')
            ->get();

        return view('recurring-bills.index', compact(
            'bills',
            'categories',
            'totalMonthlyCommitment',
            'totalActive',
            'paidCount',
            'unpaidCount',
            'totalPaidAmount',
            'totalUpcomingAmount'
        ));
    }

    /**
     * Store a newly created recurring bill in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'amount'         => ['required', 'numeric', 'min:1'],
            'category_id'    => ['nullable', 'exists:categories,id'],
            'billing_cycle'  => ['required', 'in:monthly,weekly,yearly'],
            'due_day'        => ['required', 'integer', 'between:1,31'],
            'start_date'     => ['nullable', 'date'],
            'payment_method' => ['required', 'string', 'max:50'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ]);

        $validated['user_id'] = $user->id;
        $validated['status'] = 'active';

        $user->recurringBills()->create($validated);

        return redirect()->route('recurring-bills.index')->with('status', 'Tagihan & langganan berhasil ditambahkan.');
    }

    /**
     * Update the specified recurring bill.
     */
    public function update(Request $request, RecurringBill $recurringBill): RedirectResponse
    {
        abort_if($recurringBill->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'amount'         => ['required', 'numeric', 'min:1'],
            'category_id'    => ['nullable', 'exists:categories,id'],
            'billing_cycle'  => ['required', 'in:monthly,weekly,yearly'],
            'due_day'        => ['required', 'integer', 'between:1,31'],
            'payment_method' => ['required', 'string', 'max:50'],
            'status'         => ['required', 'in:active,paused,cancelled'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ]);

        $recurringBill->update($validated);

        return redirect()->route('recurring-bills.index')->with('status', 'Tagihan berhasil diperbarui.');
    }

    /**
     * Remove the specified recurring bill.
     */
    public function destroy(Request $request, RecurringBill $recurringBill): RedirectResponse
    {
        abort_if($recurringBill->user_id !== $request->user()->id, 403);

        $recurringBill->delete();

        return redirect()->route('recurring-bills.index')->with('status', 'Tagihan berhasil dihapus.');
    }

    /**
     * One-click payment action: records a Transaction and marks bill as paid.
     */
    public function pay(Request $request, RecurringBill $recurringBill): RedirectResponse
    {
        $user = $request->user();
        abort_if($recurringBill->user_id !== $user->id, 403);

        // Fallback to first expense category if none linked
        $categoryId = $recurringBill->category_id;
        if (!$categoryId) {
            $cat = $user->categories()->where('type', 'expense')->first();
            $categoryId = $cat ? $cat->id : null;
        }

        // Record the transaction
        $user->transactions()->create([
            'category_id'      => $categoryId,
            'amount'           => $recurringBill->amount,
            'type'             => 'expense',
            'transaction_date' => Carbon::now()->toDateString(),
            'title'            => 'Pembayaran: ' . $recurringBill->name,
            'payment_method'   => $request->input('payment_method', $recurringBill->payment_method ?? 'bca'),
            'notes'            => 'Pembayaran tagihan berulang via Finora (' . ucfirst($recurringBill->billing_cycle) . ')',
        ]);

        // Update bill last paid date
        $recurringBill->update([
            'last_paid_at' => Carbon::now()->toDateString(),
        ]);

        return redirect()->route('recurring-bills.index')->with('status', "Pembayaran '{$recurringBill->name}' sebesar Rp" . number_format($recurringBill->amount, 0, ',', '.') . " berhasil dicatat ke transaksi!");
    }

    /**
     * Toggle active/paused status.
     */
    public function toggleStatus(Request $request, RecurringBill $recurringBill): RedirectResponse
    {
        abort_if($recurringBill->user_id !== $request->user()->id, 403);

        $newStatus = $recurringBill->status === 'active' ? 'paused' : 'active';
        $recurringBill->update(['status' => $newStatus]);

        $label = $newStatus === 'active' ? 'diaktifkan kembali' : 'dijeda sementara';
        return redirect()->route('recurring-bills.index')->with('status', "Tagihan {$recurringBill->name} berhasil {$label}.");
    }
}

