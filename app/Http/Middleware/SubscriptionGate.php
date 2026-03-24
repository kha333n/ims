<?php

namespace App\Http\Middleware;

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

        // Allow license routes
        if ($request->routeIs('license') || $request->routeIs('license.*') || $request->routeIs('settings.license')) {
            return $next($request);
        }

        // Allow Livewire update requests (they handle their own component context)
        if ($request->is('livewire/*') || $request->is('livewire/update')) {
            return $next($request);
        }

        // Integrity check — if files are tampered, block everything
        if (! $this->integrity->verify()) {
            abort(403, 'Application integrity check failed. Files may have been modified.');
        }

        if (! $this->license->isValid()) {
            return redirect()->route('license');
        }

        return $next($request);
    }
}
