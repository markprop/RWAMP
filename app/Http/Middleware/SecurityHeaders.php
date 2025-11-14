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

        // Sensible defaults; adjust as needed for external scripts/assets
        // For whitepaper PDF, allow embedding
        if ($request->routeIs('whitepaper.pdf')) {
            $csp = "default-src 'self'; base-uri 'self'; frame-ancestors 'self'; "
                 . "img-src 'self' data: https:; "
                 . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
                 . "font-src 'self' https://fonts.gstatic.com data:; "
                 . "script-src 'self' 'unsafe-inline' https://www.googletagmanager.com https://unpkg.com https://cdn.jsdelivr.net https://*.walletconnect.com; "
                 . "connect-src 'self' https://*.walletconnect.com wss://*.walletconnect.com https://api.coingecko.com;";
        } else {
            $csp = "default-src 'self'; base-uri 'self'; frame-ancestors 'none'; "
                 . "img-src 'self' data: https:; "
                 . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
                 . "font-src 'self' https://fonts.gstatic.com data:; "
                 . "script-src 'self' 'unsafe-inline' https://www.googletagmanager.com https://unpkg.com https://cdn.jsdelivr.net https://*.walletconnect.com; "
                 . "connect-src 'self' https://*.walletconnect.com wss://*.walletconnect.com https://api.coingecko.com;";
        }

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        
        // Allow SAMEORIGIN for whitepaper PDF to enable iframe embedding
        if ($request->routeIs('whitepaper.pdf')) {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        } else {
            $response->headers->set('X-Frame-Options', 'DENY');
        }
        
        $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');
        $response->headers->set('Permissions-Policy', "geolocation=(), camera=(), microphone=()");
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}


