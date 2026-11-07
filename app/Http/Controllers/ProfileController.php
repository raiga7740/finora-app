<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update user's preferences (currency, date_format, ledger_mode).
     */
    public function updatePreferences(Request $request, \App\Services\CurrencyRateService $currencyService): RedirectResponse
    {
        $validated = $request->validate([
            'currency'    => ['required', 'string', 'in:IDR,USD,SGD,EUR,GBP,JPY,AUD'],
            'date_format' => ['required', 'string', 'in:d M Y,d/m/Y,Y-m-d,M d, Y'],
            'ledger_mode' => ['required', 'string', 'in:strict_realtime,standard_daily'],
            'locale'      => ['required', 'string', 'in:en,id'],
            'timezone'    => ['required', 'string', 'max:50'],
        ]);

        $user = $request->user();
        $oldCurrency = $user->currency ?? 'IDR';
        $newCurrency = $validated['currency'];
        $conversionMsg = null;

        \Illuminate\Support\Facades\DB::transaction(function () use ($user, $oldCurrency, $newCurrency, $validated, $currencyService, &$conversionMsg) {
            if ($oldCurrency !== $newCurrency) {
                $summary = $currencyService->convertUserLedger($user, $oldCurrency, $newCurrency);
                $rateText = $currencyService->formatRateSummary($oldCurrency, $newCurrency);
                if ($validated['locale'] === 'en') {
                    $conversionMsg = "Currency updated to {$newCurrency}. All ledger balances, transactions, and budgets were converted using market forex rates ({$rateText}).";
                } else {
                    $conversionMsg = "Mata uang berhasil diubah ke {$newCurrency}. Seluruh saldo rekening, transaksi, dan target budget telah otomatis dikonversi dengan kurs ({$rateText}).";
                }
            }

            $user->update($validated);
        });

        session(['locale' => $validated['locale']]);

        return Redirect::route('profile.edit')
            ->with('status', 'preferences-updated')
            ->with('conversion_message', $conversionMsg);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
