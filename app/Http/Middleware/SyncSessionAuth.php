<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SyncSessionAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Session::has('user') && ! Auth::check()) {
            $userData = Session::get('user');
            if (is_array($userData)) {
                // Add remember_token so Laravel's Auth internals don't error.
                // This is NOT stored in the session — it is only passed to GenericUser.
                Auth::setUser(new GenericUser(array_merge($userData, ['remember_token' => null])));
            }
        }

        return $next($request);
    }
}
