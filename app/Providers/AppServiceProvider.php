<?php

namespace App\Providers;

use App\Models\StoreSetting;
use App\Services\OperationalNotificationService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $email = Str::lower((string) $request->input('email'));

            return Limit::perMinute(5)->by($email . '|' . $request->ip());
        });

        RateLimiter::for('public-payment-proof', function (Request $request) {
            $key = (string) $request->route('token') . '|' . $request->ip();

            return [
                Limit::perMinute(3)->by('minute|' . $key),
                Limit::perHour(10)->by('hour|' . $key),
            ];
        });

        RateLimiter::for('public-checkout', function (Request $request) {
            return [
                Limit::perMinute(5)->by('minute|' . $request->ip()),
                Limit::perHour(30)->by('hour|' . $request->ip()),
            ];
        });

        RateLimiter::for('public-cart', function (Request $request) {
            return Limit::perMinute(30)->by($request->session()->getId() . '|' . $request->ip());
        });

        RateLimiter::for('public-tracking', function (Request $request) {
            return Limit::perMinute(30)->by((string) $request->route('token') . '|' . $request->ip());
        });

        try {
            $storeSetting = Schema::hasTable('store_settings')
                ? StoreSetting::current()
                : null;
        } catch (Throwable $exception) {
            $storeSetting = null;
        }

        View::share('storeSetting', $storeSetting);

        View::composer('partials.header', function ($view) {
            $view->with(
                'operationalNotificationData',
                app(OperationalNotificationService::class)->dataFor(auth()->user())
            );
        });
    }
}
