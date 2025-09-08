<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // On vérifie si l'utilisateur est authentifié et si c'est un administrateur.
        // On utilise le guard 'web' car c'est celui utilisé par le panel Filament.
        if (! Auth::guard('web')->check() || ! Auth::guard('web')->user()->is_admin) {
            // Si ce n'est pas le cas, on refuse l'accès.
            abort(403, 'Accès non autorisé.');
        }

        return $next($request);
    }
}