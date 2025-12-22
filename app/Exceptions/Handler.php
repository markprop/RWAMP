<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // Handle CSRF token mismatch with better error handling
        if ($e instanceof \Illuminate\Session\TokenMismatchException) {
            \Log::warning('CSRF token mismatch', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_id' => auth()->id(),
            ]);

            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return response()->json([
                    'message' => 'Your session has expired. Please refresh the page and try again.',
                    'error' => 'csrf_token_expired',
                ], 419);
            }

            return redirect()
                ->back()
                ->withInput($request->except(['_token', 'password', 'password_confirmation']))
                ->with('error', 'Your session has expired. Please try again.');
        }

        // Friendly 404 page for missing models and routes
        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return response()->view('errors.404', [], 404);
        }

        // For unexpected errors in production, show a branded 503 page
        if (!$this->isHttpException($e) && !config('app.debug')) {
            \Log::error('Unhandled exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'url' => $request->fullUrl(),
                'user_id' => auth()->id(),
            ]);
            return response()->view('errors.503', [], 503);
        }

        return parent::render($request, $e);
    }
}
