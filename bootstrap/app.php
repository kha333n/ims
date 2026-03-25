<?php

use App\Http\Middleware\SubscriptionGate;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SubscriptionGate::class,
        ]);

        $middleware->redirectGuestsTo(function () {
            // First-run: no users yet → send to setup wizard, not login
            try {
                if (User::count() === 0) {
                    return route('setup');
                }
            } catch (Throwable) {
                // Table might not exist yet
            }

            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        Integration::handles($exceptions);
    })->create();
