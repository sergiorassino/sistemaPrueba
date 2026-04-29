<?php

use App\Http\Middleware\EnsureSchoolContext;
use App\Http\Middleware\EnsureStudentContext;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'school.context' => EnsureSchoolContext::class,
            'student.context' => EnsureStudentContext::class,
            'permiso'        => \App\Http\Middleware\CheckPermiso::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
