<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureMultiFactorComplete
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $role = $user->getRoleNames()->first();

        // Invitado: no necesita MFA
        if ($role === 'invitado') {
            return $next($request);
        }

        // Usuario y administrador: requieren 2FA completado
        if (!session('auth.2fa.completed')) {
            return redirect()->route('2fa.verify');
        }

        // Solo administrador: requiere además 3FA completado
        if ($role === 'administrador' && !session('auth.3fa.completed')) {
            return redirect()->route('3fa.verify');
        }

        return $next($request);
    }
}