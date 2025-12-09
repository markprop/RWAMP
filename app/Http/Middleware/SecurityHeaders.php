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
        
        // Allow unsafe-inline for tawk.to (required for dynamic styles and scripts)
        // In local environment, allow unsafe-eval for easier debugging
        $isLocal = config('app.env') === 'local';
        $unsafeEval = $isLocal ? " 'unsafe-eval'" : "";
        
        // Build CSP directives explicitly with validated URLs only
        // Connect sources - explicitly list each domain
        // Note: va.tawk.to is required for tawk.to performance logging
        // IMPORTANT: All sources must be valid URLs or CSP keywords - no placeholders
        $connectSrcList = [
            "'self'",
            "https://*.walletconnect.com",
            "wss://*.walletconnect.com",
            "https://api.coingecko.com",
            "https://www.google.com",
            // Pusher domains
            "wss://ws-ap2.pusher.com",
            "wss://ws-*.pusher.com",
            "https://sockjs-ap2.pusher.com",
            "https://sockjs-*.pusher.com",
            "https://*.pusher.com",
            "https://*.pusherapp.com",
            // Tawk.to domains (including va.tawk.to for analytics/performance)
            "https://embed.tawk.to",
            "https://*.tawk.to",
            "https://va.tawk.to",
            "https://tawk.link",
            "wss://*.tawk.to"
        ];
        
        // Filter out any sources that might contain invalid patterns
        $connectSrcList = array_filter($connectSrcList, function($source) {
            $source = trim($source);
            // Reject empty, angle brackets, or URL placeholders
            if (empty($source) || 
                strpos($source, '<') !== false || 
                strpos($source, '>') !== false ||
                stripos($source, '<URL>') !== false ||
                stripos($source, 'URL>') !== false ||
                stripos($source, '<URL') !== false ||
                stripos($source, 'URL') === 0) { // Reject if starts with "URL"
                \Log::warning('CSP: Filtered invalid connect-src', ['source' => $source]);
                return false;
            }
            return true;
        });
        
        // Additional validation - ensure no <URL> patterns remain
        $connectSrcList = array_map(function($source) {
            // Remove any angle brackets or URL placeholders that might have slipped through
            $cleaned = str_replace(['<', '>'], '', $source);
            $cleaned = preg_replace('/URL/i', '', $cleaned);
            return trim($cleaned);
        }, $connectSrcList);
        
        // Filter out any empty sources after cleaning
        $connectSrcList = array_filter($connectSrcList, function($source) {
            return !empty(trim($source));
        });
        
        // Script sources
        $scriptSrcList = [
            "'self'",
            "'unsafe-inline'",
            "https://www.googletagmanager.com",
            "https://www.google.com",
            "https://www.gstatic.com",
            "https://cdnjs.cloudflare.com",
            "https://unpkg.com",
            "https://cdn.jsdelivr.net",
            "https://*.walletconnect.com",
            "https://embed.tawk.to",
            "https://*.tawk.to"
        ];
        if ($isLocal) {
            $scriptSrcList[] = "'unsafe-eval'";
        }
        
        // Image sources
        $imgSrcList = [
            "'self'",
            "data:",
            "blob:",
            "https:",
            "https://*.tawk.to"
        ];
        
        // Style sources
        $styleSrcList = [
            "'self'",
            "'unsafe-inline'",
            "https://fonts.googleapis.com",
            "https://cdnjs.cloudflare.com"
        ];
        
        $styleSrcElemList = [
            "'self'",
            "'unsafe-inline'",
            "https://fonts.googleapis.com",
            "https://cdnjs.cloudflare.com",
            "https://embed.tawk.to",
            "https://*.tawk.to"
        ];
        
        // Font sources
        $fontSrcList = [
            "'self'",
            "https://fonts.gstatic.com",
            "data:",
            "https://*.tawk.to"
        ];
        
        // Frame sources
        $frameSrcList = [
            "'self'",
            "https://www.google.com",
            "https://embed.tawk.to",
            "https://*.tawk.to"
        ];
        
        // Helper function to validate and join CSP sources
        $joinCspSources = function($sources) {
            $validSources = [];
            
            foreach ($sources as $source) {
                // Trim whitespace
                $source = trim($source);
                
                // Reject empty sources
                if (empty($source)) {
                    continue;
                }
                
                // Allow CSP keywords
                $cspKeywords = ["'self'", "'unsafe-inline'", "'unsafe-eval'", "data:", "blob:", "https:"];
                if (in_array($source, $cspKeywords)) {
                    $validSources[] = $source;
                    continue;
                }
                
                // Reject any source containing angle brackets or URL placeholders
                if (strpos($source, '<') !== false || strpos($source, '>') !== false) {
                    \Log::warning('CSP: Rejected source with angle brackets', ['source' => $source]);
                    continue;
                }
                if (stripos($source, '<URL>') !== false || stripos($source, 'URL>') !== false || stripos($source, '<URL') !== false) {
                    \Log::warning('CSP: Rejected source with URL placeholder', ['source' => $source]);
                    continue;
                }
                
                // For URLs, must start with https:// or wss:// and be valid format
                if (strpos($source, 'https://') === 0 || strpos($source, 'wss://') === 0) {
                    // Additional validation: ensure URL doesn't contain invalid characters
                    // Allow wildcards (*) in CSP patterns, but reject other invalid chars
                    if (preg_match('/^https?:\/\/[^\s<>"\'{}|\\^`\[\]]+$/i', $source) || 
                        preg_match('/^wss?:\/\/[^\s<>"\'{}|\\^`\[\]]+$/i', $source) ||
                        preg_match('/^https?:\/\/\*\./', $source) ||  // Allow https://*.domain
                        preg_match('/^wss?:\/\/\*\./', $source) ||    // Allow wss://*.domain
                        preg_match('/^https?:\/\/[a-zA-Z0-9.-]+$/', $source) ||  // Allow exact domains
                        preg_match('/^wss?:\/\/[a-zA-Z0-9.-]+$/', $source)) {
                        $validSources[] = $source;
                    } else {
                        \Log::warning('CSP: Rejected invalid URL format', ['source' => $source]);
                    }
                    continue;
                }
                
                // Reject anything else
                \Log::warning('CSP: Rejected unknown source format', ['source' => $source]);
            }
            
            $result = implode(' ', $validSources);
            
            // Final check - ensure result doesn't contain any invalid patterns
            if (stripos($result, '<URL>') !== false || stripos($result, '<') !== false || stripos($result, '>') !== false) {
                \Log::error('CSP: joinCspSources result contains invalid patterns', ['result' => $result]);
                // Remove invalid patterns
                $result = preg_replace('/<[^>]*>/', '', $result);
                $result = preg_replace('/\s+/', ' ', trim($result));
            }
            
            return $result;
        };
        
        // Build CSP string - ensure all directives have valid sources
        $frameAncestors = $request->routeIs('whitepaper.pdf') ? "'self'" : "'none'";
        
        // Build each directive separately to ensure they're valid
        $imgSrc = $joinCspSources($imgSrcList);
        $styleSrc = $joinCspSources($styleSrcList);
        $styleSrcElem = $joinCspSources($styleSrcElemList);
        $fontSrc = $joinCspSources($fontSrcList);
        $scriptSrc = $joinCspSources($scriptSrcList);
        $frameSrc = $joinCspSources($frameSrcList);
        $connectSrc = $joinCspSources($connectSrcList);
        
        // Ensure no directive is empty - use 'self' as fallback
        if (empty($imgSrc)) $imgSrc = "'self'";
        if (empty($styleSrc)) $styleSrc = "'self' 'unsafe-inline'";
        if (empty($styleSrcElem)) $styleSrcElem = "'self' 'unsafe-inline'";
        if (empty($fontSrc)) $fontSrc = "'self' data:";
        if (empty($scriptSrc)) $scriptSrc = "'self' 'unsafe-inline'";
        if (empty($frameSrc)) $frameSrc = "'self'";
        if (empty($connectSrc)) $connectSrc = "'self'";
        
        $csp = sprintf(
            "default-src 'self'; base-uri 'self'; frame-ancestors %s; " .
            "img-src %s; " .
            "style-src %s; " .
            "style-src-elem %s; " .
            "font-src %s; " .
            "script-src %s; " .
            "frame-src %s; " .
            "connect-src %s;",
            $frameAncestors,
            $imgSrc,
            $styleSrc,
            $styleSrcElem,
            $fontSrc,
            $scriptSrc,
            $frameSrc,
            $connectSrc
        );
        
        // Final cleanup - remove any double spaces and validate
        $csp = preg_replace('/\s+/', ' ', trim($csp));
        
        // Debug: Log the CSP string to identify the issue
        \Log::info('CSP Generated', [
            'csp_length' => strlen($csp),
            'csp_preview' => substr($csp, 0, 200),
            'has_url_placeholder' => (stripos($csp, '<URL>') !== false || stripos($csp, 'URL>') !== false || stripos($csp, '<URL') !== false),
            'connect_src_part' => preg_match('/connect-src[^;]*;/', $csp, $matches) ? $matches[0] : 'not found'
        ]);
        
        // AGGRESSIVE validation - ensure no <URL> placeholder exists
        // Multiple passes to catch all variations
        $maxIterations = 3;
        for ($i = 0; $i < $maxIterations; $i++) {
            if (stripos($csp, '<URL>') === false && 
                stripos($csp, 'URL>') === false && 
                stripos($csp, '<URL') === false &&
                !preg_match('/<[^>]*URL[^>]*>/i', $csp)) {
                break; // Clean, exit loop
            }
            
            // Remove any occurrences of <URL> patterns (all variations)
            $csp = preg_replace('/<URL[^>]*>/i', '', $csp);
            $csp = preg_replace('/<[^>]*URL[^>]*>/i', '', $csp);
            $csp = preg_replace('/\s+/', ' ', trim($csp));
            
            // If connect-src specifically has issues, rebuild it
            if (preg_match('/connect-src[^;]*<[^>]*>/i', $csp)) {
                $csp = preg_replace('/connect-src[^;]*;/i', '', $csp);
                $cleanConnectSrc = $joinCspSources($connectSrcList);
                $csp = rtrim($csp, '; ') . '; connect-src ' . $cleanConnectSrc . ';';
                $csp = preg_replace('/\s+/', ' ', trim($csp));
            }
        }
        
        // Final sanitization - remove ANY remaining angle brackets and URL placeholders
        $csp = str_replace(['<', '>'], '', $csp);
        // Remove any remaining URL placeholder patterns
        $csp = preg_replace('/<URL[^>]*>/i', '', $csp);
        $csp = preg_replace('/URL>/i', '', $csp);
        $csp = preg_replace('/\s+/', ' ', trim($csp));

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        
        // Allow SAMEORIGIN for whitepaper PDF to enable iframe embedding
        if ($request->routeIs('whitepaper.pdf')) {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        } else {
            $response->headers->set('X-Frame-Options', 'DENY');
        }
        
        $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');
        // Allow microphone for voice recording in chat
        $response->headers->set('Permissions-Policy', "geolocation=(self), camera=(), microphone=(self)");
        
        // Use replace() instead of set() to override any browser extension-injected CSP headers
        // This ensures our clean CSP is used even if extensions try to modify it
        $response->headers->remove('Content-Security-Policy');
        $response->headers->remove('Content-Security-Policy-Report-Only');
        
        // Final check - verify CSP doesn't contain any invalid patterns before setting
        // Remove any potential problematic patterns
        $csp = preg_replace('/[<>]/', '', $csp); // Remove any angle brackets
        // Remove any URL placeholder patterns (case-insensitive)
        $csp = preg_replace('/<URL[^>]*>/i', '', $csp);
        $csp = preg_replace('/URL>/i', '', $csp);
        $csp = preg_replace('/<URL/i', '', $csp);
        // Remove any standalone "URL" that might be invalid
        $csp = preg_replace('/\bURL\b/i', '', $csp);
        $csp = preg_replace('/\s+/', ' ', trim($csp)); // Clean spaces
        // Remove any double semicolons or trailing semicolons
        $csp = preg_replace('/;+/', ';', $csp);
        $csp = trim($csp, '; ');
        if (!empty($csp) && substr($csp, -1) !== ';') {
            $csp .= ';';
        }
        
        // Log final CSP for debugging (only in local/dev)
        if ($isLocal || config('app.debug')) {
            \Log::info('Final CSP Header', [
                'csp' => $csp,
                'length' => strlen($csp),
                'connect_src' => preg_match('/connect-src\s+([^;]+);/', $csp, $m) ? $m[1] : 'not found'
            ]);
        }
        
        $response->headers->set('Content-Security-Policy', $csp, true); // true = replace existing

        return $response;
    }
}


