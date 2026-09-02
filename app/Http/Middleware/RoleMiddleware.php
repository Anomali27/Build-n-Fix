<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     * @param  string  ...$roles  Allowed roles for this route
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Session::has('user')) {
            return redirect()->route('login')->with('auth_error', 'Please sign in to access this page.');
        }

        $user = Session::get('user');
        $userRole = $user['role'] ?? '';

        if (! in_array($userRole, $roles, true)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
