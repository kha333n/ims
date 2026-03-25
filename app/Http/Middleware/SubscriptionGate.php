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

        // Allow Livewire update requests
        if ($request->is('livewire/*') || $request->is('livewire-*/update') || $request->is('livewire/update')) {
            return $next($request);
        }

        // First-run check: if no users exist, redirect to setup BEFORE license check
        try {
            if (User::count() === 0) {
                return redirect()->route('setup');
            }
        } catch (\Throwable) {
            // Table might not exist yet
        }

        // Integrity check
        if (! $this->integrity->verify()) {
            abort(403, 'Application integrity check failed. Files may have been modified.');
        }

        // License check — users exist but license invalid
        // If not logged in, send to login first; if logged in, send to license page
        if (! $this->license->isValid()) {
            if (! auth()->check()) {
                return redirect()->route('login');
            }

            return redirect()->route('license');
        }

        return $next($request);
    }
}
