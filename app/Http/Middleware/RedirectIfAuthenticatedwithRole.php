<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectIfAuthenticatedwithRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {

        if (auth()->check()) {
            $role = auth()->user()->role;

            switch ($role) {
                case 'admin':
                    return redirect()->route('admin.dashboard');
                case 'gestion_calidad':
                    return redirect()->route('gestion_calidad.dashboard');
                case 'personal_tecnico':
                    return redirect()->route('personal_tecnico.dashboard');
                case 'pasante':
                    return redirect()->route('pasante.dashboard');
            }
        }

        return $next($request);
    }
}
