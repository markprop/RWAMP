<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class TabAuthService
{
    /**
     * Get the authenticated user for the current tab
     * Falls back to main session auth if no tab-specific user exists
     */
    public static function user($request = null): ?User
    {
        $request = $request ?? request();
        $tabId = $request->cookie('tab_session_id');
        
        if ($tabId) {
            // Check if middleware set a tab-specific user
            $tabUser = $request->attributes->get('tab_authenticated_user');
            if ($tabUser) {
                return $tabUser;
            }
            
            // Fallback: check cache directly
            $tabUserId = Cache::get("tab_user:{$tabId}");
            if ($tabUserId) {
                return User::find($tabUserId);
            }

            // If a tab ID exists but no mapping is found, treat as unauthenticated
            // (do NOT fall back to the global session user – tabs must be independent).
            return null;
        }
        
        // No tab ID cookie -> fall back to main session authentication
        return Auth::user();
    }
    
    /**
     * Set the authenticated user for a specific tab
     */
    public static function setTabUser(string $tabId, int $userId, int $hours = 24): void
    {
        Cache::put("tab_user:{$tabId}", $userId, now()->addHours($hours));
    }
    
    /**
     * Clear the authenticated user for a specific tab
     */
    public static function clearTabUser(string $tabId): void
    {
        Cache::forget("tab_user:{$tabId}");
    }
    
    /**
     * Check if a tab has a specific user authenticated
     */
    public static function hasTabUser(string $tabId): bool
    {
        return Cache::has("tab_user:{$tabId}");
    }
}



