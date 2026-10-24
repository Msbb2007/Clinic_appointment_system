<?php

namespace App\Providers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        //تعریف Gate برای بررسی اینکه آیا کاربر پروفایل بیمار دارد//
        Gate::define('is-patient', function ($user) {
            return $user->patient !== null;
        });
    }
}
