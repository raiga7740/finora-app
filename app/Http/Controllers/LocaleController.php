<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Switch user interface locale (ID / EN).
     */
    public function switch(Request $request, string $locale): RedirectResponse
    {
        if (in_array($locale, ['en', 'id'], true)) {
            session(['locale' => $locale]);

            if ($user = $request->user()) {
                $user->update(['locale' => $locale]);
            }

            app()->setLocale($locale);
        }

        return redirect()->back();
    }
}

