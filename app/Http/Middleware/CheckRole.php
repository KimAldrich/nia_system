<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role)
    {
        // 1. Make sure the user is actually logged in
        if (!auth()->check()) {
            return redirect('/login');
        }

        $userRole = auth()->user()->role;

        // 2. The Master Key: Admins bypass ALL role restrictions
        if ($userRole === 'admin') {
            return $next($request);
        }

        // 3. Normal Check: Does the user's role match the required team role?
        if ($userRole === $role) {
            return $next($request);
        }

        // 4. If they aren't the team member and aren't an admin, kick them out
        abort(403, 'Unauthorized action.');
    }
}
