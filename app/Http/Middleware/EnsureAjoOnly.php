<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAjoOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    // app/Http/Middleware/EnsureAjoOnly.php
public function handle($request, Closure $next)
{
    // Platform admins can still open legacy if you want:
    // if (auth()->user()?->hasRole('admin')) return $next($request);

    return redirect()->route('dashboard')
    ->with('error', 'That feature is no longer part of Ajo.'); // or redirect to dashboard with message
}
}
