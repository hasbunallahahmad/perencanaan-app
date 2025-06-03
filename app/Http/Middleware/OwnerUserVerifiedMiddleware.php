<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OwnerUserVerifiedMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (is_null($request->user()->owner_verified_at)) {
            abort(Response::HTTP_FORBIDDEN, 'Your account must be verified by an admin.');
        }

        return $next($request);
    }
}
