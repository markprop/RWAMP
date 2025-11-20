<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        
        // Check if user is authenticated
        if (!$user) {
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return response()->json([
                    'error' => 'Authentication required'
                ], 401)->header('Content-Type', 'application/json');
            }
            return redirect()->route('login');
        }
        
        // Check if user has one of the required roles
        $userRole = $user->role;
        $hasRole = false;
        
        // Handle multiple roles passed as arguments
        foreach ($roles as $role) {
            // Handle comma-separated roles (e.g., 'admin,reseller')
            $roleList = explode(',', $role);
            foreach ($roleList as $r) {
                if (trim($r) === $userRole) {
                    $hasRole = true;
                    break 2;
                }
            }
        }
        
        if (!$hasRole) {
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                return response()->json([
                    'error' => 'You do not have permission to access this resource'
                ], 403)->header('Content-Type', 'application/json');
            }
            return redirect()->route('home')->with('error', 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}


