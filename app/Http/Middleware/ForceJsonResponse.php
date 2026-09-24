<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Forces /api/* requests to always be treated as JSON, so validation and
 * exception responses never fall back to a redirect (e.g. missing Accept header).
 */
class ForceJsonResponse
{
    public function handle(Request $request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
