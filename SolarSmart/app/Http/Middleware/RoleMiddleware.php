<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
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
