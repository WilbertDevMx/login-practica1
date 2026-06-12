<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;

/**
 * Middleware de bloqueo durante modo mantenimiento.
 *
 * Extiende el middleware base de Laravel para retornar HTTP 503
 * en todas las peticiones mientras la aplicación esté en modo
 * mantenimiento (`php artisan down`).
 *
 * Las URIs listadas en `$except` permanecen accesibles durante
 * el mantenimiento, útil para rutas de healthcheck, webhooks
 * críticos o paneles de monitoreo que no deben interrumpirse.
 *
 * Se registra como middleware global en {@see \App\Http\Kernel::$middleware}.
 *
 * @package App\Http\Middleware
 */
class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * URIs accesibles mientras el modo mantenimiento está activo.
     *
     * Ejemplos de rutas que típicamente se excluyen:
     * - `/healthcheck`      : Para que el load balancer detecte el estado del servidor.
     * - `/webhook/payments` : Para no perder eventos de pasarelas de pago.
     * - `/admin/up`         : Para que el equipo pueda verificar el despliegue.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}