<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\LoginLog;
/**
 * Controlador del tercer factor de autenticación (3FA) para administradores.
 *
 * Implementa una capa adicional de verificación exclusiva para usuarios con
 * rol `administrador`, enviando un código alfanumérico de 6 caracteres al
 * correo registrado con una validez de 10 minutos. Gestiona el ciclo completo:
 * generación, envío, validación y reenvío del código, usando la sesión de
 * Laravel como almacén temporal.
 *
 * Claves de sesión utilizadas:
 * - `auth.3fa.code`        : Código 3FA vigente.
 * - `auth.3fa.expires_at`  : Timestamp de expiración del código ({@see \Carbon\Carbon}).
 * - `auth.3fa.completed`   : Flag que indica que el flujo 3FA fue completado.
 * - `auth.2fa.pending`     : Flag del paso 2FA previo, limpiado al completar 3FA.
 *
 * @package App\Http\Controllers\Auth
 */
class ThreeFactorController extends Controller
{
    /**
     * Registra los middlewares del controlador.
     *
     * Aplica dos capas de protección a todos los métodos:
     * 1. `auth`  : El usuario debe estar autenticado.
     * 2. Closure : El usuario autenticado debe tener el rol `administrador`;
     *              cualquier otro rol es redirigido a `/dashboard`.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            /** @var \App\Models\User|null $user */
            $user = Auth::user();
            if ($user->getRoleNames()->first() !== 'administrador') {
                return redirect()->route('login');
            }next($request);
        });
    }

    /**
     * Muestra el formulario de verificación del tercer factor (3FA).
     *
     * Si el flujo 3FA ya fue completado en esta sesión, redirige al dashboard
     * previsto sin volver a mostrar el formulario. En caso contrario, genera
     * y envía un nuevo código solo si no existe uno vigente (no expirado),
     * evitando envíos redundantes ante recargas de página.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     *         Redirige a `/dashboard` si 3FA ya está completo,
     *         o muestra la vista 'auth.3fa_verify'.
     */
    public function showVerifyForm()
    {
        if (session('auth.3fa.completed')) {
            return redirect()->route('dashboard.admin');
        }

        $expiresAt     = session('auth.3fa.expires_at');
        $codigoVigente = session()->has('auth.3fa.code')
                         && $expiresAt
                         && now()->lt($expiresAt);

        if (!$codigoVigente) {
            $this->generateAndSendCode();
        }

        return view('auth.3fa_verify');
    }

    /**
     * Genera un código 3FA aleatorio, lo almacena en sesión y lo envía por correo.
     *
     * El código es una cadena alfanumérica de 6 caracteres generada con
     * {@see \Illuminate\Support\Str::random()}. Se persiste en sesión junto
     * con su timestamp de expiración (10 minutos desde la generación).
     * El envío se realiza mediante la vista de email `emails.3fa_code`,
     * que recibe el código y el objeto usuario.
     *
     * @return void
     */
    protected function generateAndSendCode(): void
    {
        $code = Str::random(6);
        $user = Auth::user();

        session([
            'auth.3fa.code'       => $code,
            'auth.3fa.expires_at' => now()->addMinutes(10),
        ]);

        Mail::send('emails.3fa_code', ['code' => $code, 'user' => $user], function ($message) use ($user) {
            $message->to($user->email, $user->name)
                    ->subject('Código de Verificación de Tres Factores');
        });
    }

    /**
     * Valida el código 3FA enviado por el usuario.
     *
     * Comprueba en orden:
     * 1. Que exista un código en sesión y no haya expirado; si expiró,
     *    devuelve error e indica al frontend que debe reenviar.
     * 2. Que el código ingresado coincida exactamente con el de sesión;
     *    si no coincide, devuelve error sin consumir el código.
     * 3. Si es correcto, limpia las claves de sesión intermedias
     *    (`auth.2fa.pending`, `auth.3fa.code`, `auth.3fa.expires_at`),
     *    marca el flujo como completado y redirige al dashboard de admin.
     *
     * @param  \Illuminate\Http\Request  $request  Debe contener:
     *                                             - string $verification_code  Código introducido por el usuario
     *
     * @return \Illuminate\Http\RedirectResponse  Redirige a 'dashboard.admin' si el código es válido,
     *                                            o de vuelta al formulario con errores si no lo es
     */
    public function verify(Request $request)
    {
        $request->validate(['verification_code' => 'required|string']);

        if (!session()->has('auth.3fa.code') || now()->gt(session('auth.3fa.expires_at'))) {
            return back()->withErrors(['verification_code' => 'El código ha expirado. Se ha enviado uno nuevo.'])
                         ->with('resend', true);
        }

        if ($request->verification_code !== session('auth.3fa.code')) {
            LoginLog::create([
                'email'      => Auth::user()->email,  // ← email del usuario autenticado
                'ip' => \App\Helpers\IpHelper::ubicacion($request),
                'exitoso'    => false,
                'error_en'  => '3fa_invalide_code',
                'user_agent' => $request->userAgent(),
            ]);
            return back()->withErrors(['verification_code' => 'El código ingresado es incorrecto.']);
        }

        session()->forget(['auth.2fa.pending', 'auth.3fa.code', 'auth.3fa.expires_at']);
        session(['auth.3fa.completed' => true]);

        return redirect()->route('dashboard.admin')->with('success', '¡Bienvenido, Administrador!');
    }

    /**
     * Fuerza el reenvío de un nuevo código 3FA al correo del administrador.
     *
     * Invalida el código actual eliminando sus claves de sesión antes de
     * generar y enviar uno nuevo, garantizando que el código anterior quede
     * inutilizable incluso si no había expirado.
     *
     * @return \Illuminate\Http\RedirectResponse  Redirige de vuelta al formulario
     *                                            con mensaje de confirmación de envío
     */
    public function resendCode()
    {
        session()->forget(['auth.3fa.code', 'auth.3fa.expires_at']);
        $this->generateAndSendCode();

        return back()->with('status', 'Se ha enviado un nuevo código a tu correo electrónico.');
    }
}