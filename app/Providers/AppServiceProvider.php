<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enforce HTTPS asset and route URLs in production or when APP_URL is https
        if (config('app.env') === 'production' || str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // Automatically enforce user's selected locale and timezone across all views
        view()->composer('*', function ($view) {
            $user = auth()->user();
            if ($user && !empty($user->locale) && in_array($user->locale, ['en', 'id'], true)) {
                app()->setLocale($user->locale);
            } elseif (session()->has('locale') && in_array(session('locale'), ['en', 'id'], true)) {
                app()->setLocale(session('locale'));
            }

            if ($user && !empty($user->timezone)) {
                try {
                    date_default_timezone_set($user->timezone);
                } catch (\Throwable) {
                    // Ignore invalid timezone
                }
            }
        });

        Blade::directive('currency', function ($expression) {
            return "<?php echo format_currency({$expression}); ?>";
        });

        Blade::directive('currencySymbol', function () {
            return "<?php echo currency_symbol(); ?>";
        });

        Blade::directive('userDate', function ($expression) {
            return "<?php echo format_user_date({$expression}); ?>";
        });
    }
}
