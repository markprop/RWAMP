<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use App\Services\TabAuthService;

class Authenticate extends Middleware
{
    /**
     * Determine if the user is logged in to any of the given guards.
     * Override to check tab-specific authentication first.
     */
    protected function authenticate($request, array $guards)
    {
        $tabId = $request->cookie('tab_session_id');

        if ($tabId) {
            // For tabs with a per-tab ID, rely solely on tab-specific auth.
            $tabUser = TabAuthService::user($request);

            if ($tabUser) {
                $request->setUserResolver(function () use ($tabUser) {
                    return $tabUser;
                });
                return;
            }

            // Tab has an ID but no mapped user => unauthenticated for this tab.
            $this->unauthenticated($request, $guards);
        }

        // No tab ID cookie -> fall back to standard session-based authentication.
        parent::authenticate($request, $guards);
    }
    
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }
}
