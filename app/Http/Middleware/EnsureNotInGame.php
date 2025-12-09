<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotInGame
{
    /**
     * Handle an incoming request.
     * Prevents users in game from performing non-game RWAMP mutations
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && $user->is_in_game) {
            return response()->json([
                'success' => false,
                'message' => 'You are currently in a game session. Please exit the game first to perform this action.',
            ], 403);
        }

        return $next($request);
    }
}
