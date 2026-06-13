<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FAQRCode\Google2FA;
use App\Models\User;
use App\Models\TwoFactorLog;
use App\Models\LoginLog;

/**
 * Controlador del segundo factor de autenticación (2FA) basado en TOTP.
 *
 * Gestiona el flujo completo de Google Authenticator mediante la librería
 * PragmaRX\Google2FA, cubriendo dos escenarios:
 *
 * - **Setup inicial**: el usuario no tiene secreto TOTP registrado; se genera
 *   uno, se muestra el QR para escanearlo con la app autenticadora y se
 *   persiste en base de datos solo tras verificar el primer código válido.
 *
 * - **Verificación recurrente**: el usuario ya tiene secreto registrado;
 *   solo se valida el OTP contra el secreto almacenado.
 *
 * Tras verificación exitosa, el flujo bifurca según rol:
 * - `administrador` → continúa al flujo 3FA ({@see ThreeFactorController}).
 * - `usuario`       → accede directamente al dashboard de usuario.
 *
 * Claves de sesión utilizadas:
 * - `auth.2fa.completed`    : Flag que indica que el 2FA fue superado.
 * - `auth.2fa.pending`      : Flag del paso anterior (login), limpiado al completar.
 * - `auth.2fa.setup_secret` : Secreto TOTP temporal durante el setup inicial.
 *
 * @package App\Http\Controllers\Auth
 */
class TwoFactorController extends Controller
{
    /**
     * Registra los middlewares del controlador.
     *
     * Aplica `auth` a todos los métodos, garantizando que el usuario haya
     * superado el primer factor (credenciales) antes de acceder al 2FA.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Muestra el formulario de verificación del segundo factor (2FA).
     *
     * Evalúa el estado del usuario y responde con una de tres acciones:
     *
     * 1. Si `auth.2fa.completed` está en sesión → redirige al dashboard previsto.
     * 2. Si el usuario **no** tiene secreto TOTP registrado → genera un secreto,
     *    construye la URL del QR inline y retorna la vista en modo setup (`isNew = true`),
     *    almacenando el secreto temporal en `auth.2fa.setup_secret`.
     * 3. Si el usuario **ya** tiene secreto → retorna la vista solo con el formulario
     *    OTP (`isNew = false`), sin exponer ni regenerar el secreto.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     *         Redirige a `/dashboard` si 2FA ya está completo, o muestra
     *         'auth.2fa_verify' con las variables:
     *         - bool        $isNew    Indica si es configuración inicial.
     *         - string|null $qrCode   URL inline del QR (solo en setup inicial).
     *         - string|null $secret   Secreto TOTP en texto plano (solo en setup inicial).
     */
    public function showVerifyForm(Request $request)
    {
        if (session('auth.2fa.completed')) {
            return redirect()->intended('/dashboard');
        }

        /** @var \App\Models\User|null $user */
        $user      = Auth::user();
        $google2fa = new Google2FA();

        if (empty($user->google2fa_secret)) {
            $secret = $google2fa->generateSecretKey();

            $qrCodeUrl = $google2fa->getQRCodeInline(
                config('app.name'),
                $user->email,
                $secret
            );

            session(['auth.2fa.setup_secret' => $secret]);

            return view('auth.2fa_verify', [
                'qrCode' => $qrCodeUrl,
                'secret' => $secret,
                'isNew'  => true,
            ]);
        }

        return view('auth.2fa_verify', [
            'isNew' => false,
        ]);
    }

    /**
     * Valida el código OTP (One-Time Password) enviado por el usuario.
     *
     * Ejecuta las siguientes etapas:
     * 1. Determina el secreto TOTP activo: usa `google2fa_secret` del modelo
     *    si existe, o `auth.2fa.setup_secret` de sesión si está en setup inicial.
     * 2. Si no hay secreto disponible, devuelve error indicando reinicio de sesión.
     * 3. Verifica el OTP con {@see Google2FA::verifyKey()} y registra el intento
     *    (exitoso o fallido) en {@see TwoFactorLog}.
     * 4. Si el OTP es inválido, redirige de vuelta con error.
     * 5. Si era setup inicial, persiste el secreto en el modelo y elimina la
     *    clave temporal de sesión.
     * 6. Marca `auth.2fa.completed` en sesión y limpia `auth.2fa.pending`.
     * 7. Bifurca según rol:
     *    - `administrador` → redirige a {@see ThreeFactorController::showVerifyForm()}.
     *    - `usuario`       → registra en {@see LoginLog} y redirige al dashboard.
     *
     * @param  \Illuminate\Http\Request  $request  Debe contener:
     *                                             - string $one_time_password  Código TOTP de 6 dígitos
     *
     * @return \Illuminate\Http\RedirectResponse  Redirige según rol si el OTP es válido,
     *                                            o de vuelta al formulario con errores si no lo es
     */
    public function verify(Request $request)
    {
        $request->validate(['one_time_password' => 'required|string']);

        /** @var \App\Models\User $user */
        $user      = Auth::user();
        $google2fa = new Google2FA();

        $secret = $user->google2fa_secret ?? session('auth.2fa.setup_secret');

        if (empty($secret)) {
            return back()->withErrors(['one_time_password' => 'No hay secret configurado. Inicia sesión de nuevo.']);
        }

        $valid = $google2fa->verifyKey($secret, $request->one_time_password);

        TwoFactorLog::create([
            'user_id'    => $user->id,
            'email'      => $user->email,
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
            'action'     => 'verify_attempt',
            'successful' => $valid,
            'message'    => $valid ? 'OTP correcto' : 'Código OTP inválido',
        ]);

        if (!$valid) {
            return back()->withErrors(['one_time_password' => 'El código de verificación no es válido.']);
        }

        if (empty($user->google2fa_secret)) {
            $user->google2fa_secret = $secret;
            $user->save();
            session()->forget('auth.2fa.setup_secret');
        }

        session(['auth.2fa.completed' => true]);
        session()->forget('auth.2fa.pending');

        $role = $user->getRoleNames()->first();

        if ($role === 'administrador') {
            return redirect()->route('3fa.verify');
        }

        LoginLog::create([
            'email'      => $user->email,
            'ip'         => $request->ip(),
            'exitoso'    => true,
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('dashboard.usuario')->with('success', '¡Bienvenido!');
    }
}