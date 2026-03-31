<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    private const DEACTIVATED_MESSAGE = 'Your account is deactivated by the admin. Please contact the admin to reactivate your account.';

    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && !Auth::user()->is_active) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('deactivated_message', self::DEACTIVATED_MESSAGE)
                ->withErrors(['email' => self::DEACTIVATED_MESSAGE]);
        }

        return $next($request);
    }
}
