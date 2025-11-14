<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureKycApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Allow access if user is an investor (KYC approved)
        if ($user && $user->role === 'investor') {
            return $next($request);
        }

        // Allow access to KYC routes and profile
        $allowedRoutes = ['kyc.show', 'kyc.submit', 'profile.show'];
        if ($user && in_array($request->route()?->getName(), $allowedRoutes)) {
            return $next($request);
        }

        // Redirect to KYC page if user is not an investor
        if ($user) {
            return redirect()->route('kyc.show')->with('warning', 'Complete KYC to access this feature.');
        }

        return $next($request);
    }
}
