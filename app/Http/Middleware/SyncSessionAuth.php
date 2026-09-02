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
        if (Session::has('user')) {
            $userData = Session::get('user');
            if (is_array($userData)) {
                $id = (int) ($userData['id'] ?? $userData['user_id'] ?? 1);
                $fullUser = array_merge($userData, [
                    'id' => $id,
                    'user_id' => $id,
                    'remember_token' => null,
                ]);
                $genericUser = new GenericUser($fullUser);
                Auth::setUser($genericUser);
            }
        }

        return $next($request);
    }
}
