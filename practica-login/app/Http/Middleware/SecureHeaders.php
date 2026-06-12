<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de cabeceras de seguridad HTTP.
 *
 * Añade y sanitiza cabeceras de seguridad en todas las respuestas
 * del grupo `web`, siguiendo recomendaciones de OWASP y Mozilla
 * Observatory para mitigar ataques comunes de lado cliente.
 *
 * Cabeceras aplicadas:
 *
 * | Cabecera                    | Valor                              | Propósito                                                             |
 * |-----------------------------|------------------------------------|-----------------------------------------------------------------------|
 * | `X-Frame-Options`           | `DENY`                             | Previene clickjacking bloqueando el embedding en iframes.             |
 * | `X-Content-Type-Options`    | `nosniff`                          | Evita que el navegador infiera el MIME type (MIME sniffing).          |
 * | `X-XSS-Protection`          | `1; mode=block`                    | Activa el filtro XSS del navegador (legacy; complementa la CSP).      |
 * | `Referrer-Policy`           | `strict-origin-when-cross-origin`  | Limita la información de referencia enviada en peticiones externas.   |
 * | `Content-Security-Policy`   | Ver detalle en `handle()`          | Restringe las fuentes de carga de recursos para mitigar XSS.          |
 * | `Strict-Transport-Security` | `max-age=31536000; includeSubDomains` | Fuerza HTTPS por 1 año (solo en producción).                       |
 * | `X-Powered-By`              | *(eliminada)*                      | Oculta el stack tecnológico al eliminar la cabecera.                  |
 * | `Server`                    | *(vacía)*                          | Oculta la versión e identidad del servidor web.                       |
 *
 * Se registra en el grupo `web` en {@see \App\Http\Kernel::$middlewareGroups}.
 *
 * @package App\Http\Middleware
 */
class SecureHeaders
{
    /**
     * Procesa la petición y añade las cabeceras de seguridad a la respuesta.
     *
     * El middleware opera en modo *post-processing*: primero deja pasar la
     * petición al siguiente middleware o controlador (`$next($request)`), y
     * luego modifica la respuesta antes de devolverla al cliente.
     *
     * **Política CSP aplicada:**
     * - `default-src 'self'`           : Solo recursos del mismo origen por defecto.
     * - `style-src 'self' 'unsafe-inline'` : Estilos propios e inline (requerido por Blade).
     * - `script-src 'self' 'unsafe-inline' https://www.google.com/recaptcha/ https://www.gstatic.com/recaptcha/`
     *                                  : Scripts propios, inline y los dominios de Google reCAPTCHA.
     * - `frame-src https://www.google.com/recaptcha/` : Solo permite iframes del widget reCAPTCHA.
     * - `img-src 'self' data:`         : Imágenes del mismo origen y URIs `data:` (ej. QR codes en base64).
     *
     * **HSTS** solo se activa en entorno `production` para evitar bloqueos
     * durante desarrollo local con HTTP.
     *
     * @param  \Illuminate\Http\Request                      $request
     * @param  \Closure(\Illuminate\Http\Request): Response  $next
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->remove('X-Powered-By');
        $response->headers->set('Server', '');
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; " .
            "style-src 'self' 'unsafe-inline'; " .
            "script-src 'self' 'unsafe-inline' https://www.google.com/recaptcha/ https://www.gstatic.com/recaptcha/; " .
            "frame-src https://www.google.com/recaptcha/; " .
            "img-src 'self' data:;"
        );

        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}