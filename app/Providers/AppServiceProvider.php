<?php

namespace App\Providers;

use App\Models\StoreSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
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
        try {
            $storeSetting = Schema::hasTable('store_settings')
                ? StoreSetting::current()
                : null;
        } catch (Throwable $exception) {
            $storeSetting = null;
        }

        View::share('storeSetting', $storeSetting);
    }
}