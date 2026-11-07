<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetUserLocaleAndTimezone
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 1. Set Application Locale
        $locale = $user->locale ?? session('locale', config('app.locale', 'en'));
        if (in_array($locale, ['en', 'id'], true)) {
            App::setLocale($locale);
        }

        // 2. Set Application Timezone
        $timezone = $user->timezone ?? session('timezone', config('app.timezone', 'Asia/Jakarta'));
        if (!empty($timezone)) {
            try {
                date_default_timezone_set($timezone);
            } catch (\Throwable) {
                // Ignore invalid timezones
            }
        }

        return $next($request);
    }
}

