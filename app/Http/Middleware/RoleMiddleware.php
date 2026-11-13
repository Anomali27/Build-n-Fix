<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        if (! Auth::check()) {
            return redirect()->route('login')->with('auth_error', 'Please sign in to access this page.');
        }

        $user = Auth::user();
        $userRole = $user->role ?? '';

        if (! in_array($userRole, $roles, true)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengakses fitur ini.',
                ], 403);
            }

            if (in_array($userRole, ['admin', 'owner'], true)) {
                return redirect()->route('dashboard')
                    ->with('error', 'Akses ditolak. Fitur Checkout hanya dapat diakses oleh akun Customer.');
            }

            if (method_exists($user, 'homeUrl')) {
                return redirect($user->homeUrl())
                    ->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.');
            }

            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
