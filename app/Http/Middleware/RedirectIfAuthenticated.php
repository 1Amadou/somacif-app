<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Logique de redirection personnalisée
                switch ($guard) {
                    case 'client':
                        return redirect()->route('client.dashboard');
                    case 'livreur':
                        return redirect()->route('livreur.dashboard');
                    default:
                        // Pour les admins (guard 'web') et les autres cas
                        return redirect(RouteServiceProvider::HOME);
                }
            }
        }

        return $next($request);
    }
}