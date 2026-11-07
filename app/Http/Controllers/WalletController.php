<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletController extends Controller
{
    /**
     * Display a listing of wallets and accounts.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $wallets = $user->wallets()
            ->orderBy('is_primary', 'desc')
            ->orderBy('name')
            ->get();

        $currencyService = app(\App\Services\CurrencyRateService::class);
        $userCurrency = strtoupper($user->currency ?? 'IDR');

        // Calculate balances converted to user's primary currency
        $totalNetWorth = 0;
        $totalBank = 0;
        $totalEwallet = 0;
        $totalCash = 0;
        $totalInvestment = 0;

        foreach ($wallets as $wallet) {
            $bal = $wallet->current_balance;
            $converted = $currencyService->convert($bal, $wallet->currency ?? $userCurrency, $userCurrency);
            $totalNetWorth += $converted;

            if ($wallet->type === 'bank') {
                $totalBank += $converted;
            } elseif ($wallet->type === 'ewallet') {
                $totalEwallet += $converted;
            } elseif ($wallet->type === 'cash') {
                $totalCash += $converted;
            } elseif ($wallet->type === 'investment') {
                $totalInvestment += $converted;
            }
        }

        // Recent transfers
        $recentTransfers = $user->transactions()
            ->where('type', 'transfer')
            ->with(['wallet', 'destinationWallet'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(6)
            ->get();

        return view('wallets.index', compact(
            'wallets',
            'currencyService',
            'totalNetWorth',
            'totalBank',
            'totalEwallet',
            'totalCash',
            'totalInvestment',
            'recentTransfers'
        ));
    }

    /**
     * Store a newly created wallet.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'type'            => ['required', 'in:bank,ewallet,cash,investment'],
            'currency'        => ['nullable', 'string', 'in:IDR,USD,EUR,GBP,SGD,AUD,JPY'],
            'account_number'  => ['nullable', 'string', 'max:100'],
            'initial_balance' => ['required', 'numeric', 'min:0'],
            'color'           => ['nullable', 'string', 'max:20'],
            'is_primary'      => ['nullable', 'boolean'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ]);

        $validated['user_id'] = $user->id;
        $validated['currency'] = strtoupper($validated['currency'] ?? $user->currency ?? 'IDR');
        $validated['color'] = $validated['color'] ?? '#00c078';
        $validated['is_primary'] = !empty($validated['is_primary']) || $user->wallets()->count() === 0;

        if ($validated['is_primary']) {
            $user->wallets()->update(['is_primary' => false]);
        }

        $user->wallets()->create($validated);

        return redirect()->route('wallets.index')->with('status', 'Rekening / Dompet baru berhasil ditambahkan.');
    }

    /**
     * Update the specified wallet.
     */
    public function update(Request $request, Wallet $wallet): RedirectResponse
    {
        abort_if($wallet->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'type'            => ['required', 'in:bank,ewallet,cash,investment'],
            'currency'        => ['nullable', 'string', 'in:IDR,USD,EUR,GBP,SGD,AUD,JPY'],
            'account_number'  => ['nullable', 'string', 'max:100'],
            'initial_balance' => ['required', 'numeric', 'min:0'],
            'color'           => ['nullable', 'string', 'max:20'],
            'is_primary'      => ['nullable', 'boolean'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ]);

        $validated['currency'] = strtoupper($validated['currency'] ?? $wallet->currency ?? 'IDR');
        $validated['color'] = $validated['color'] ?? '#00c078';
        $validated['is_primary'] = !empty($validated['is_primary']);

        if ($validated['is_primary']) {
            $request->user()->wallets()->where('id', '!=', $wallet->id)->update(['is_primary' => false]);
        }

        $wallet->update($validated);

        return redirect()->route('wallets.index')->with('status', 'Data dompet berhasil diperbarui.');
    }

    /**
     * Remove the specified wallet.
     */
    public function destroy(Request $request, Wallet $wallet): RedirectResponse
    {
        abort_if($wallet->user_id !== $request->user()->id, 403);

        $user = $request->user();

        // Check if primary
        if ($wallet->is_primary && $user->wallets()->count() > 1) {
            $newPrimary = $user->wallets()->where('id', '!=', $wallet->id)->first();
            if ($newPrimary) {
                $newPrimary->update(['is_primary' => true]);
            }
        }

        $wallet->delete();

        return redirect()->route('wallets.index')->with('status', 'Rekening / Dompet berhasil dihapus.');
    }

    /**
     * Execute internal balance transfer between wallets (supports cross-currency exchange).
     */
    public function transfer(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'source_wallet_id'      => ['required', 'exists:wallets,id'],
            'destination_wallet_id' => ['required', 'exists:wallets,id', 'different:source_wallet_id'],
            'amount'                => ['required', 'numeric', 'min:0.01'],
            'destination_amount'    => ['nullable', 'numeric', 'min:0.01'],
            'transaction_date'      => ['nullable', 'date'],
            'notes'                 => ['nullable', 'string', 'max:500'],
        ]);

        $source = $user->wallets()->findOrFail($validated['source_wallet_id']);
        $dest   = $user->wallets()->findOrFail($validated['destination_wallet_id']);

        $currencyService = app(\App\Services\CurrencyRateService::class);
        $sourceCurrency = strtoupper($source->currency ?? $user->currency ?? 'IDR');
        $destCurrency   = strtoupper($dest->currency ?? $user->currency ?? 'IDR');

        if ($sourceCurrency === $destCurrency) {
            $destAmount = (float) $validated['amount'];
            $rate = 1.0;
        } else {
            if (!empty($validated['destination_amount']) && (float) $validated['destination_amount'] > 0) {
                $destAmount = (float) $validated['destination_amount'];
                $rate = $destAmount / (float) $validated['amount'];
            } else {
                $rate = $currencyService->getExchangeRate($sourceCurrency, $destCurrency);
                $destAmount = $currencyService->convert($validated['amount'], $sourceCurrency, $destCurrency);
            }
        }

        $baseAmount = $currencyService->convert($validated['amount'], $sourceCurrency, $user->currency ?? 'IDR');

        $date = !empty($validated['transaction_date'])
            ? Carbon::parse($validated['transaction_date'])->toDateString()
            : Carbon::now()->toDateString();

        $user->transactions()->create([
            'wallet_id'             => $source->id,
            'destination_wallet_id' => $dest->id,
            'amount'                => $validated['amount'],
            'currency'              => $sourceCurrency,
            'destination_amount'    => $destAmount,
            'exchange_rate'         => $rate,
            'base_amount'           => $baseAmount,
            'type'                  => 'transfer',
            'payment_method'        => $source->type,
            'transaction_date'      => $date,
            'title'                 => "Transfer: {$source->name} → {$dest->name}",
            'notes'                 => $validated['notes'] ?? 'Internal transfer between accounts',
        ]);

        $formattedSource = $source->formatBalance($validated['amount']);
        $formattedDest   = $dest->formatBalance($destAmount);

        $msg = $sourceCurrency !== $destCurrency
            ? "Transfer {$formattedSource} ({$source->name}) → {$formattedDest} ({$dest->name}) successfully recorded!"
            : "Transfer {$formattedSource} from {$source->name} to {$dest->name} successfully recorded!";

        return redirect()->route('wallets.index')->with('status', $msg);
    }
}

