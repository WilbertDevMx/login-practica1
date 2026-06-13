<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

/**
 * Middleware de autenticación de la aplicación.
 *
 * Extiende el middleware base de Laravel para personalizar el comportamiento
 * de redirección cuando un usuario no autenticado intenta acceder a una
 * ruta protegida.
 *
 * Se registra bajo el alias `auth` en {@see \App\Http\Kernel::$middlewareAliases}
 * y es utilizado por todos los controladores y rutas que requieren sesión activa,
 * incluyendo el flujo MFA ({@see \App\Http\Middleware\EnsureMultiFactorComplete}).
 *
 * @package App\Http\Middleware
 */
class Authenticate extends Middleware
{
    /**
     * Determina la ruta de redirección para usuarios no autenticados.
     *
     * Diferencia entre dos tipos de cliente:
     * - **Peticiones JSON** (APIs, AJAX, SPA): devuelve `null` para que el
     *   middleware base retorne un HTTP 401 en lugar de una redirección,
     *   ya que un cliente JSON no puede procesar una respuesta HTML de login.
     * - **Peticiones web normales**: redirige a la ruta con nombre `login`
     *   para que el usuario pueda autenticarse mediante el formulario.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return string|null  URL de redirección, o `null` para responder con HTTP 401.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }
}