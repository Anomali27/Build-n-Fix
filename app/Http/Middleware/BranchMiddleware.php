<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class BranchMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     * @param  string  ...$branches  Allowed branches for this route
     */
    public function handle(Request $request, Closure $next, string ...$branches): Response
    {
        if (! Session::has('user')) {
            return redirect()->route('login')->with('auth_error', 'Please sign in to access this page.');
        }

        $user = Session::get('user');
        $userBranch = $user['branch'] ?? '';
        $userRole = $user['role'] ?? '';

        // Owner with branch 'all' has access to every branch
        if ($userRole === 'owner' && $userBranch === 'all') {
            return $next($request);
        }

        if (! empty($branches) && ! in_array($userBranch, $branches, true)) {
            abort(403, 'You do not have permission to access this branch.');
        }

        return $next($request);
    }
}
