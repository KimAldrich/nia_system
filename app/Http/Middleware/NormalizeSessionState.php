<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class NormalizeSessionState
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $request->session()->forget([
                'is_guest',
                'guest_terms_accepted',
            ]);
        } else {
            $request->session()->forget([
                'agreed_to_terms',
            ]);
        }

        return $next($request);
    }
}
