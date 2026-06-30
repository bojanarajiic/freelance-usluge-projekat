<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Usage: ->middleware('role:admin') ili 'role:client,freelancer'
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // ako nema specificiranih rola, pusti (fallback)
        if (empty($roles)) {
            return $next($request);
        }

        // uporedi sa korisnikovom rolom
        if (!in_array($user->role, $roles, true)) {
            return response()->json(['message' => 'Forbidden: insufficient role'], 403);
        }

        return $next($request);
    }
}
