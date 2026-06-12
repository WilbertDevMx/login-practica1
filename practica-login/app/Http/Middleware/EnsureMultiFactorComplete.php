<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Middleware de verificación de autenticación multifactor completa.
 *
 * Protege las rutas que requieren que el usuario haya superado todos
 * los factores de autenticación correspondientes a su rol, y además
 * garantiza que cada usuario solo pueda acceder al dashboard asignado
 * a su rol, previniendo escalada horizontal entre dashboards.
 *
 * Matriz de requisitos por rol:
 *
 * | Rol            | 2FA | 3FA | Dashboard destino     |
 * |----------------|-----|-----|-----------------------|
 * | `invitado`     | No  | No  | `dashboard.invitado`  |
 * | `usuario`      | Sí  | No  | `dashboard.usuario`   |
 * | `administrador`| Sí  | Sí  | `dashboard.admin`     |
 *
 * Claves de sesión evaluadas:
 * - `auth.2fa.completed` : Flag establecido por {@see \App\Http\Controllers\Auth\TwoFactorController::verify()}.
 * - `auth.3fa.completed` : Flag establecido por {@see \App\Http\Controllers\Auth\ThreeFactorController::verify()}.
 *
 * @package App\Http\Middleware
 */
class EnsureMultiFactorComplete
{
    /**
     * Procesa la petición entrante verificando el estado MFA del usuario.
     *
     * Ejecuta las comprobaciones en el siguiente orden:
     *
     * 1. **Sin sesión autenticada** → redirige a `login`.
     * 2. **Rol `invitado`** → no requiere MFA, pero solo puede acceder a
     *    `dashboard.invitado`; cualquier otra ruta lo devuelve allí.
     * 3. **2FA incompleto** (usuarios y administradores) → redirige a `2fa.verify`.
     * 4. **3FA incompleto** (solo administradores) → redirige a `3fa.verify`.
     * 5. **Dashboard incorrecto para el rol** → redirige al dashboard que
     *    corresponde al rol según el mapa interno, o a `login` si el rol
     *    no está contemplado.
     * 6. Todas las comprobaciones superadas → cede el control al siguiente
     *    middleware o al controlador de destino.
     *
     * @param  \Illuminate\Http\Request  $request  Petición HTTP entrante.
     * @param  \Closure(\Illuminate\Http\Request): \Illuminate\Http\Response  $next
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $role = $user->getRoleNames()->first();

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