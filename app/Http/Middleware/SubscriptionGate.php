<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\IntegrityChecker;
use App\Services\LicenseManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionGate
{
    public function __construct(
        private LicenseManager $license,
        private IntegrityChecker $integrity,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Skip gate in testing only
        if (app()->environment('testing') && ! config('ims.license.enforce_in_tests', false)) {
            return $next($request);
        }

        // Allow auth routes (login, setup, reset, logout)
        if ($request->routeIs('login') || $request->routeIs('setup') || $request->routeIs('password.reset') || $request->routeIs('logout')) {
            return $next($request);
        }

        // Allow license routes
        if ($request->routeIs('license') || $request->routeIs('license.*') || $request->routeIs('settings.license')) {
            return $next($request);
        }

        // Allow Livewire update requests (NativePHP adds hash: livewire-XXXX/update)
        if ($request->is('livewire/*') || $request->is('livewire-*/update') || $request->is('livewire/update')) {
            return $next($request);
        }

        // Integrity check
        if (! $this->integrity->verify()) {
            abort(403, 'Application integrity check failed. Files may have been modified.');
        }

        // License check
        if (! $this->license->isValid()) {
            return redirect()->route('license');
        }

        // First-run check: if no users exist, redirect to setup
        try {
            if (User::count() === 0 && ! $request->routeIs('setup')) {
                return redirect()->route('setup');
            }
        } catch (\Throwable) {
            // Table might not exist yet
        }

        return $next($request);
    }
}
