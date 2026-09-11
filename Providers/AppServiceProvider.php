<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema; // ✅ THIS LINE IS MISSING
use Illuminate\Support\Facades\Vite;
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
        Schema::defaultStringLength(191);

    
        // Share session timeout with all views
        view()->composer('*', function ($view) {
            $view->with('sessionTimeout', \App\Models\SystemSetting::getValue('session_timeout', 120));
        });
    }
}
