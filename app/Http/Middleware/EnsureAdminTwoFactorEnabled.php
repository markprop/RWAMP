<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminTwoFactorEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && $user->role === 'admin') {
            // Redirect only if 2FA not enabled at all. Allow access once a secret exists,
            // even if not yet confirmed (confirmation happens on next login).
            if (empty($user->two_factor_secret)) {
                if (! $request->routeIs('admin.2fa.setup')) {
                    return redirect()->route('admin.2fa.setup');
                }
            }
        }
        return $next($request);
    }
}


