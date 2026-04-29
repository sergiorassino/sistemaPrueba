<?php

namespace App\Providers;

use App\Auth\AlumnoUserProvider;
use App\Auth\ProfesorUserProvider;
use App\Support\SchoolContext;
use App\Support\StudentContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SchoolContext::class, function ($app) {
            return SchoolContext::fromSession();
        });

        $this->app->singleton(StudentContext::class, function ($app) {
            return StudentContext::fromSession();
        });
    }

    public function boot(): void
    {
        Auth::provider('profesor', function ($app, array $config) {
            return new ProfesorUserProvider();
        });

        Auth::provider('alumno', function ($app, array $config) {
            return new AlumnoUserProvider();
        });
    }
}
