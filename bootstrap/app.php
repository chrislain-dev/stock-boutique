<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // TODO: Fix Laravel 11 middleware alias syntax
        // $middleware->alias('permission', App\Http\Middleware\CheckPermission::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Custom exception handling is configured in the Handler class
        // If needed, additional renderers/reporters can be registered here
    })->create();
