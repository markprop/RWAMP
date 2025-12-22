<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;

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
            // Log CSRF token mismatch for debugging
            Log::warning('CSRF token mismatch', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => auth()->id(),
                'session_id' => $request->session()->getId(),
            ]);

            // JSON / API requests get a clear 419 response with retry guidance
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return response()->json([
                    'message' => 'CSRF token expired. Please refresh the page and try again.',
                    'error' => 'csrf_token_expired',
                    'retry_url' => $request->fullUrl(),
                ], 419)->header('X-CSRF-Token-Expired', 'true');
            }

            // For browser requests, redirect back with flash message if possible
            // Otherwise render a lightweight auto-refresh page
            if ($request->hasSession() && $request->session()->isStarted()) {
                return redirect()
                    ->back()
                    ->withInput($request->except(['_token', 'password', 'password_confirmation']))
                    ->with('error', 'Your session has expired. Please try again.')
                    ->setStatusCode(419);
            }

            // Fallback: render auto-refresh page
            return response()
                ->view('errors.csrf-refresh', [
                    'retry_url' => $request->fullUrl(),
                ])
                ->setStatusCode(419);
        }
    }
}

