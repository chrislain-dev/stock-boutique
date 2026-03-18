<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        if (!$permission) {
            return $next($request);
        }

        if (!Auth::check()) {
            abort(401, 'Authentification requise.');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasPermission($permission)) {
            abort(403, 'Permission refusée: '.$permission);
        }

        return $next($request);
    }
}
