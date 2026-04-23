<?php

namespace App\Providers;

use App\Auth\ProfesorUserProvider;
use App\Support\SchoolContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SchoolContext::class, function ($app) {
            return SchoolContext::fromSession();
        });
    }

    public function boot(): void
    {
        Auth::provider('profesor', function ($app, array $config) {
            return new ProfesorUserProvider();
        });
    }
}
