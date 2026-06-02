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
        // Invitado: no necesita MFA pero solo puede ver su dashboard
        if ($role === 'invitado') {
            if ($request->route()->getName() !== 'dashboard.invitado') {
                return redirect()->route('dashboard.invitado');
            }
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

        // Verificar que el usuario esté en su dashboard correcto
        $dashboards = [
            'invitado'      => 'dashboard.invitado',
            'usuario'       => 'dashboard.usuario',
            'administrador' => 'dashboard.admin',
        ];

        $rutaCorrecta = $dashboards[$role] ?? 'login';
        $rutaActual   = $request->route()->getName();

        if ($rutaActual !== $rutaCorrecta) {
            return redirect()->route($rutaCorrecta);
        }

        return $next($request);
    }
}