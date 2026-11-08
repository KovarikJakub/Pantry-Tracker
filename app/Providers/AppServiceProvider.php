<?php

namespace App\Providers;

use App\Services\Authenticator;
use Illuminate\Support\Facades\Auth;
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
        // Gates and policies ask Laravel's auth for the current user; point it at our own session-based Authenticator.
        Auth::resolveUsersUsing(fn () => $this->app->make(Authenticator::class)->user());
    }
}
