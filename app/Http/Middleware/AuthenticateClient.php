<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateClient
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session()->has('authenticated_client_id')) {
            return redirect()->route('nos-offres')->with('error', 'Vous devez être connecté pour accéder à cette page.');
        }
        
        return $next($request);
    }
}