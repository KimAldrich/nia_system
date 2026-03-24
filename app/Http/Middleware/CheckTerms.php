<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTerms
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if they are logged in BUT have not agreed during this current session
        if (auth()->check() && !session('agreed_to_terms')) {
            return redirect()->route('terms.show');
        }

        return $next($request);
    }
}
