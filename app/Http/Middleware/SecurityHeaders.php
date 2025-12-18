<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // Sensible defaults; adjust as needed for external scripts/assets.
        // Keep this CSP simple and static to avoid invalid placeholder sources like "<URL>".

        $isLocal = config('app.env') === 'local';

        // Allow embedding for whitepaper, secure receipt routes, and legacy storage assets (used inside iframes/modals)
        if (
            $request->routeIs('whitepaper.pdf') ||
            $request->routeIs('receipts.payment') ||
            $request->is('storage/payment-receipts/*') ||
            $request->is('storage/withdrawal-receipts/*')
        ) {
            $frameAncestors = "'self'";
        } else {
            $frameAncestors = "'none'";
        }
        
        // Build a static CSP string with only known-good sources.
        $scriptUnsafeEval = $isLocal ? " 'unsafe-eval'" : '';

        $csp = "default-src 'self'; "
            . "base-uri 'self'; "
            . "frame-ancestors {$frameAncestors}; "
            . "img-src 'self' data: blob: https: https://*.tawk.to; "
            . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; "
            . "style-src-elem 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com https://embed.tawk.to https://*.tawk.to; "
            . "font-src 'self' https://fonts.gstatic.com data: https://*.tawk.to; "
            . "script-src 'self' 'unsafe-inline'{$scriptUnsafeEval} https://www.googletagmanager.com https://www.google.com https://www.gstatic.com https://cdnjs.cloudflare.com https://unpkg.com https://cdn.jsdelivr.net https://*.walletconnect.com https://embed.tawk.to https://*.tawk.to; "
            . "frame-src 'self' https://www.google.com https://embed.tawk.to https://*.tawk.to; "
            . "connect-src 'self' https://api.coingecko.com https://www.google.com https://*.walletconnect.com wss://*.walletconnect.com "
            . "wss://ws-ap2.pusher.com wss://ws-*.pusher.com https://sockjs-ap2.pusher.com https://sockjs-*.pusher.com https://*.pusher.com https://*.pusherapp.com "
            . "https://embed.tawk.to https://*.tawk.to https://va.tawk.to https://tawk.link wss://*.tawk.to;";

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        
        // Allow SAMEORIGIN for whitepaper PDF, secure receipt routes, and storage receipt assets
        if (
            $request->routeIs('whitepaper.pdf') ||
            $request->routeIs('receipts.payment') ||
            $request->is('storage/payment-receipts/*') ||
            $request->is('storage/withdrawal-receipts/*')
        ) {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        } else {
            $response->headers->set('X-Frame-Options', 'DENY');
        }
        
        $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');
        // Allow microphone for voice recording in chat
        $response->headers->set('Permissions-Policy', "geolocation=(self), camera=(), microphone=(self)");
        
        // Use replace() instead of set() to override any browser extension-injected CSP headers.
        $response->headers->remove('Content-Security-Policy');
        $response->headers->remove('Content-Security-Policy-Report-Only');

        $response->headers->set('Content-Security-Policy', $csp, true); // true = replace existing

        return $response;
    }
}


