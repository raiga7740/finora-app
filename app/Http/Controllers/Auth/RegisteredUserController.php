<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'currency' => ['nullable', 'string', 'in:IDR,USD,SGD,EUR,GBP,JPY,AUD'],
            'locale'   => ['nullable', 'string', 'in:en,id'],
            'timezone' => ['nullable', 'string', 'max:50'],
        ]);

        $curr = strtoupper($request->input('currency', 'IDR'));
        $defaultLocale = $request->input('locale', ($curr === 'IDR') ? 'id' : 'en');
        $defaultTz = $request->input('timezone', ($curr === 'USD') ? 'America/New_York' : (($curr === 'EUR') ? 'Europe/Paris' : (($curr === 'GBP') ? 'Europe/London' : (($curr === 'SGD') ? 'Asia/Singapore' : 'Asia/Jakarta'))));

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'currency' => $curr,
            'date_format' => ($curr === 'USD') ? 'M d, Y' : 'd M Y',
            'ledger_mode' => 'strict_realtime',
            'locale' => $defaultLocale,
            'timezone' => $defaultTz,
        ]);

        // Seed initial default categories for the new user
        $defaultCategories = [
            ['name' => 'Food & Dining', 'type' => 'expense', 'description' => 'Groceries, dining out, and daily sustenance'],
            ['name' => 'Bills & Utilities', 'type' => 'expense', 'description' => 'Electricity, water, wifi & monthly utilities'],
            ['name' => 'Transportation', 'type' => 'expense', 'description' => 'Commute, fuel, train, MRT, transit passes'],
            ['name' => 'Shopping & Lifestyle', 'type' => 'expense', 'description' => 'Clothing, gadgets & personal items'],
            ['name' => 'Entertainment', 'type' => 'expense', 'description' => 'Subscriptions, streaming, movies & outings'],
            ['name' => 'Salary / Inflow', 'type' => 'income', 'description' => 'Primary monthly salary and employment inflow'],
            ['name' => 'Freelance & Projects', 'type' => 'income', 'description' => 'Contract, freelance, and side revenue'],
            ['name' => 'Emergency Reserve', 'type' => 'saving', 'description' => 'Liquidity reserve for unexpected expenses'],
        ];

        foreach ($defaultCategories as $cat) {
            $user->categories()->create($cat);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
