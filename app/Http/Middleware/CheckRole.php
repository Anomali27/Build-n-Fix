<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $roles  Comma‑separated list of allowed roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $roles)
    {
        // If the user is not authenticated, redirect to login (or abort)
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        // Assuming the User model has a "role" attribute (string) or a method "hasRole"
        $allowed = array_map('trim', explode(',', $roles));

        // Support both a simple "role" attribute and a Spatie‑like hasRole() method.
        $hasRole = false;
        if (method_exists($user, 'hasRole')) {
            foreach ($allowed as $role) {
                if ($user->hasRole($role)) {
                    $hasRole = true;
                    break;
                }
            }
        } else {
            // Fallback to a plain "role" column on the users table
            $hasRole = in_array($user->role, $allowed, true);
        }

        if (! $hasRole) {
            // You can customize the response – abort with 403 or redirect.
            abort(Response::HTTP_FORBIDDEN, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
?>
