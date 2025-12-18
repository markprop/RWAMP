<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Session\TokenMismatchException;

/**
 * Wraps the existing VerifyCsrfToken middleware so we can gracefully
 * handle expired/invalid tokens (419) without losing the custom logic
 * that already exists in VerifyCsrfToken (e.g. GET /logout bypass).
 */
class HandleExpiredCsrf extends VerifyCsrfToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            return parent::handle($request, $next);
        } catch (TokenMismatchException $e) {
            // JSON / API requests get a clear 419 response
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return response()->json([
                    'message' => 'CSRF token expired. Please refresh and try again.',
                ], 419);
            }

            // For browser requests, render a lightweight auto-refresh page
            return response()
                ->view('errors.csrf-refresh')
                ->setStatusCode(419);
        }
    }
}

