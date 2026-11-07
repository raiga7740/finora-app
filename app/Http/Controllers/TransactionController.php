<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->user()->transactions()->with('category');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%'.$request->search.'%')
                  ->orWhere('payment_method', 'like', '%'.$request->search.'%')
                  ->orWhere('notes', 'like', '%'.$request->search.'%');
            });
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->paginate(15)->withQueryString();
        $categories = $request->user()->categories()->orderBy('name')->get();

        return view('transactions.index', compact('transactions', 'categories'));
    }

    public function create(Request $request): View
    {
        $categories = $request->user()->categories()->orderBy('name')->get();
        $wallets = $request->user()->wallets()->orderBy('is_primary', 'desc')->orderBy('name')->get();
        return view('transactions.create', compact('categories', 'wallets'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'amount'           => ['required', 'numeric', 'min:0.01'],
            'type'             => ['required', 'in:income,expense,transfer'],
            'wallet_id'        => ['nullable', 'exists:wallets,id'],
            'payment_method'   => ['nullable', 'string', 'max:100'],
            'category_id'      => ['nullable', 'exists:categories,id'],
            'transaction_date' => ['required', 'date'],
            'notes'            => ['nullable', 'string'],
        ]);

        if (empty($validated['wallet_id'])) {
            $primaryWallet = $user->wallets()->where('is_primary', true)->first();
            $validated['wallet_id'] = $primaryWallet ? $primaryWallet->id : null;
        }

        if (!empty($validated['wallet_id'])) {
            $w = $user->wallets()->find($validated['wallet_id']);
            if ($w) {
                if (empty($validated['payment_method'])) {
                    $validated['payment_method'] = $w->name;
                }
                $validated['currency'] = $w->currency ?? $user->currency ?? 'IDR';
            }
        }

        $txCurrency = $validated['currency'] ?? $user->currency ?? 'IDR';
        $validated['currency'] = $txCurrency;
        $validated['base_amount'] = app(\App\Services\CurrencyRateService::class)->convert(
            $validated['amount'],
            $txCurrency,
            $user->currency ?? 'IDR'
        );

        $user->transactions()->create($validated);

        return redirect()->route('transactions.index')
            ->with('status', 'Transaction added successfully.');
    }

    public function edit(Transaction $transaction): View
    {
        $this->authorize('update', $transaction);
        $categories = auth()->user()->categories()->orderBy('name')->get();
        $wallets = auth()->user()->wallets()->orderBy('is_primary', 'desc')->orderBy('name')->get();
        return view('transactions.edit', compact('transaction', 'categories', 'wallets'));
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('update', $transaction);
        $user = $request->user();

        $validated = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'amount'           => ['required', 'numeric', 'min:0.01'],
            'type'             => ['required', 'in:income,expense,transfer'],
            'wallet_id'        => ['nullable', 'exists:wallets,id'],
            'payment_method'   => ['nullable', 'string', 'max:100'],
            'category_id'      => ['nullable', 'exists:categories,id'],
            'transaction_date' => ['required', 'date'],
            'notes'            => ['nullable', 'string'],
        ]);

        if (!empty($validated['wallet_id'])) {
            $w = $user->wallets()->find($validated['wallet_id']);
            if ($w) {
                if (empty($validated['payment_method'])) {
                    $validated['payment_method'] = $w->name;
                }
                $validated['currency'] = $w->currency ?? $user->currency ?? 'IDR';
            }
        }

        $txCurrency = $validated['currency'] ?? $transaction->currency ?? $user->currency ?? 'IDR';
        $validated['currency'] = $txCurrency;
        $validated['base_amount'] = app(\App\Services\CurrencyRateService::class)->convert(
            $validated['amount'],
            $txCurrency,
            $user->currency ?? 'IDR'
        );

        $transaction->update($validated);

        return redirect()->route('transactions.index')
            ->with('status', 'Transaction updated successfully.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);
        $transaction->delete();

        return redirect()->route('transactions.index')
            ->with('status', 'Transaction deleted successfully.');
    }
}
