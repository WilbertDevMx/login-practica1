<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Psr\Log\LogLevel;

/**
 * Manejador de excepciones personalizado para la aplicación.
 *
 * Configura los niveles de log, excepciones no reportadas,
 * campos que no se deben mostrar en sesión, y callbacks de reporte.
 */
class Handler extends ExceptionHandler
{
    /**
     * Lista de tipos de excepción con sus niveles de log personalizados.
     *
     * @var array<class-string<\Throwable>, LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * Lista de tipos de excepción que no deben ser reportadas.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * Lista de inputs que nunca se deben guardar en sesión en excepciones de validación.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Registra los callbacks de manejo de excepciones para la aplicación.
     *
     * @return void
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
}