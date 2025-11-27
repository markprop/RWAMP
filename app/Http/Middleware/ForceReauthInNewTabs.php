<?php

namespace App\Http\Middleware;

use App\Services\TabAuthService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForceReauthInNewTabs
{
    /**
     * If a tab has a tab_session_id cookie but no tab-specific user mapping,
     * treat this tab as requiring a fresh login (without touching the main session).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tabId = $request->cookie('tab_session_id');

        if ($tabId && Auth::check() && !TabAuthService::hasTabUser($tabId)) {
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return response()->json([
                    'message' => 'Please log in to continue.',
                ], 401);
            }

            return redirect()
                ->route('login')
                ->with('info', 'Please log in to continue.');
        }

        return $next($request);
    }
}


