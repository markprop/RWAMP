<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class TabSessionAuth
{
    /**
     * Handle an incoming request.
     * 
     * This middleware manages per-tab authentication by:
     * 1. Reading the tab_session_id cookie
     * 2. Checking cache for tab-specific user mapping
     * 3. Setting the authenticated user for this tab if found
     * 4. Preserving main session for backward compatibility
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tabId = $request->cookie('tab_session_id');
        
        if ($tabId) {
            // Check if this tab has a specific user mapped
            $tabUserId = Cache::get("tab_user:{$tabId}");

            if ($tabUserId) {
                // Load the user for this tab
                $user = User::find($tabUserId);

                if ($user) {
                    // Expose tab-specific user on the request
                    $request->attributes->set('tab_authenticated_user', $user);
                    $request->attributes->set('tab_session_id', $tabId);

                    // Override the global auth user for this request only
                    // so Auth::user() / auth()->user() are tab-aware everywhere.
                    Auth::setUser($user);
                }
            }
        }
        
        return $next($request);
    }
}



