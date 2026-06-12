<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de redirección para usuarios ya autenticados.
 *
 * Protege rutas de acceso público (login, registro) redirigiendo
 * a los usuarios que ya tienen una sesión activa, evitando que
 * accedan a formularios de autenticación innecesariamente.
 *
 * Se registra bajo el alias `guest` en {@see \App\Http\Kernel::$middlewareAliases}.
 *
 * @package App\Http\Middleware
 */
class RedirectIfAuthenticated
{
    /**
     * Procesa la petición entrante verificando si el usuario ya está autenticado.
     *
     * Itera sobre los guards proporcionados (o el guard por defecto si no se
     * especifica ninguno) y redirige a {@see \App\Providers\RouteServiceProvider::HOME}
     * ante el primer guard que tenga una sesión activa.
     *
     * Si ningún guard tiene sesión activa, cede el control al siguiente
     * middleware o al controlador de destino.
     *
     * @param  \Illuminate\Http\Request                                              $request
     * @param  \Closure(\Illuminate\Http\Request): \Symfony\Component\HttpFoundation\Response  $next
     * @param  string                                                                ...$guards  Guards a evaluar (ej. 'web', 'api').
     *                                                                                          Si se omiten, se usa el guard por defecto.
     *
     * @return \Symfony\Component\HttpFoundation\Response  La respuesta del siguiente middleware,
     *                                                     o una redirección a HOME si ya está autenticado.
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}