<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CustomerAuth
{
    /**
     * Handle an incoming request.
     * Checks if customer user session exists without triggering Eloquent/Database calls.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Session::has('user')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('login')->with('info', 'Please sign in to access your profile settings.');
        }

        return $next($request);
    }
}
