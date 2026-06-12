<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

/**
 * Middleware de cifrado de cookies.
 *
 * Extiende el middleware base de Laravel para cifrar y descifrar
 * automáticamente todas las cookies de la aplicación usando la
 * clave definida en `APP_KEY` del archivo `.env`.
 *
 * Cualquier cookie no listada en `$except` será cifrada en la respuesta
 * y descifrada en la petición entrante de forma transparente, sin
 * requerir cambios en controladores ni vistas.
 *
 * Se registra en el grupo `web` en {@see \App\Http\Kernel::$middlewareGroups}.
 *
 * @package App\Http\Middleware
 */
class EncryptCookies extends Middleware
{
    /**
     * Nombres de cookies que deben permanecer sin cifrar.
     *
     * Casos típicos para excluir una cookie:
     * - Cookies leídas por JavaScript del lado cliente (el cifrado las hace ilegibles para JS).
     * - Cookies de terceros que llegan pre-formateadas (ej. analytics, A/B testing).
     * - Cookies de SDKs externos que gestionan su propio cifrado.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}