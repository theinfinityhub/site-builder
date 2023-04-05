<?php

namespace Kavi\SiteEditor\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyCsrfTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->method() !== 'GET' && ! $request->is('api/*')) {
            $token = $request->header('X-CSRF-TOKEN') ?: $request->input('_token');

            if (! $token || ! $request->session()->token() === $token) {
                return response()->json(['message' => 'CSRF token mismatch.'], 403);
            }
        }
        return $next($request);
    }
}
