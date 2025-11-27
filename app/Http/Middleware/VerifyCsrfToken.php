<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Logout can be accessed via GET (for expired sessions) or POST (with CSRF)
        // Our custom logout handler in AuthController handles both cases
    ];
    
    /**
     * Determine if the request should be excluded from CSRF verification.
     * Allow GET requests to /logout to work even with expired sessions.
     */
    public function handle($request, \Closure $next)
    {
        // Allow GET requests to /logout to bypass CSRF (for expired sessions)
        if ($request->is('logout') && $request->isMethod('get')) {
            return $next($request);
        }
        
        return parent::handle($request, $next);
    }
}
