<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

/**
 * Kernel HTTP de la aplicación.
 *
 * Punto central de configuración del pipeline de middlewares de Laravel.
 * Define tres capas de middlewares:
 *
 * - **Global** (`$middleware`)        : Se ejecutan en absolutamente todas las peticiones.
 * - **Por grupo** (`$middlewareGroups`): Asignados colectivamente a rutas `web` o `api`.
 * - **Por alias** (`$middlewareAliases`): Registrados con nombre corto para uso en rutas
 *                                        individuales o grupos personalizados.
 *
 * Middlewares propios del proyecto registrados aquí:
 * - {@see \App\Http\Middleware\SecureHeaders}          : Añade cabeceras de seguridad HTTP (CSP, HSTS, etc.).
 * - {@see \App\Http\Middleware\EnsureMultiFactorComplete} : Protege rutas que requieren MFA completo (alias `mfa.complete`).
 *
 * @package App\Http
 */
class Kernel extends HttpKernel
{
    /**
     * Middlewares globales de la aplicación.
     *
     * Se ejecutan en cada petición HTTP entrante, independientemente
     * de la ruta o grupo al que pertenezca.
     *
     * - {@see \App\Http\Middleware\TrustProxies}                                    : Define proxies de confianza para resolver IPs y esquemas correctamente (ej. detrás de un load balancer).
     * - {@see \Illuminate\Http\Middleware\HandleCors}                               : Gestiona las cabeceras CORS para peticiones cross-origin.
     * - {@see \App\Http\Middleware\PreventRequestsDuringMaintenance}                : Retorna 503 cuando la aplicación está en modo mantenimiento.
     * - {@see \Illuminate\Foundation\Http\Middleware\ValidatePostSize}              : Valida que el body de la petición no supere `post_max_size` de PHP.
     * - {@see \App\Http\Middleware\TrimStrings}                                     : Recorta espacios en blanco de los campos string del request.
     * - {@see \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull}     : Convierte strings vacíos a `null` para evitar inconsistencias en la DB.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * Grupos de middlewares de la aplicación.
     *
     * Cada grupo se asigna en bloque a un conjunto de rutas. Laravel registra
     * automáticamente los grupos `web` y `api` en el RouteServiceProvider.
     *
     * **Grupo `web`** — Para rutas con interfaz de usuario:
     * - {@see \App\Http\Middleware\EncryptCookies}                                  : Cifra y descifra todas las cookies de la aplicación.
     * - {@see \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse}             : Adjunta las cookies encoladas durante el ciclo de vida a la respuesta.
     * - {@see \Illuminate\Session\Middleware\StartSession}                          : Inicializa y gestiona la sesión HTTP.
     * - {@see \Illuminate\View\Middleware\ShareErrorsFromSession}                   : Inyecta los errores de validación de sesión en todas las vistas (`$errors`).
     * - {@see \App\Http\Middleware\VerifyCsrfToken}                                 : Verifica el token CSRF en peticiones de escritura (POST, PUT, PATCH, DELETE).
     * - {@see \Illuminate\Routing\Middleware\SubstituteBindings}                    : Resuelve los route model bindings declarados en rutas.
     * - {@see \App\Http\Middleware\SecureHeaders}                                   : Añade cabeceras de seguridad HTTP personalizadas (ej. CSP, X-Frame-Options, HSTS).
     *
     * **Grupo `api`** — Para rutas de API sin estado:
     * - {@see \Illuminate\Routing\Middleware\ThrottleRequests}                      : Aplica rate limiting configurado bajo el nombre `api`.
     * - {@see \Illuminate\Routing\Middleware\SubstituteBindings}                    : Resuelve los route model bindings declarados en rutas de API.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\SecureHeaders::class,
        ],

        'api' => [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * Aliases de middlewares de la aplicación.
     *
     * Permiten referenciar un middleware por un nombre corto en la definición
     * de rutas (ej. `->middleware('mfa.complete')`) en lugar de usar el FQCN.
     *
     * | Alias              | Clase                                                                          | Descripción                                                    |
     * |--------------------|--------------------------------------------------------------------------------|----------------------------------------------------------------|
     * | `auth`             | {@see \App\Http\Middleware\Authenticate}                                       | Redirige a login si el usuario no está autenticado.            |
     * | `auth.basic`       | {@see \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth}                   | Autenticación HTTP Basic.                                      |
     * | `auth.session`     | {@see \Illuminate\Session\Middleware\AuthenticateSession}                      | Invalida sesión si las credenciales del usuario cambiaron.     |
     * | `cache.headers`    | {@see \Illuminate\Http\Middleware\SetCacheHeaders}                             | Define cabeceras de caché para la respuesta.                   |
     * | `can`              | {@see \Illuminate\Auth\Middleware\Authorize}                                   | Verifica permisos mediante Laravel Gates/Policies.             |
     * | `guest`            | {@see \App\Http\Middleware\RedirectIfAuthenticated}                            | Redirige al dashboard si el usuario ya está autenticado.       |
     * | `password.confirm` | {@see \Illuminate\Auth\Middleware\RequirePassword}                             | Exige reconfirmación de contraseña antes de acceder.           |
     * | `signed`           | {@see \App\Http\Middleware\ValidateSignature}                                  | Valida la firma criptográfica de URLs firmadas.                |
     * | `throttle`         | {@see \Illuminate\Routing\Middleware\ThrottleRequests}                         | Rate limiting configurable por ruta.                           |
     * | `verified`         | {@see \Illuminate\Auth\Middleware\EnsureEmailIsVerified}                       | Exige que el email del usuario esté verificado.                |
     * | `mfa.complete`     | {@see \App\Http\Middleware\EnsureMultiFactorComplete}                          | Exige que todos los factores MFA del usuario estén completados.|
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [
        'auth'             => \App\Http\Middleware\Authenticate::class,
        'auth.basic'       => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session'     => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers'    => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can'              => \Illuminate\Auth\Middleware\Authorize::class,
        'guest'            => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed'           => \App\Http\Middleware\ValidateSignature::class,
        'throttle'         => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified'         => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'mfa.complete'     => \App\Http\Middleware\EnsureMultiFactorComplete::class,
    ];
}