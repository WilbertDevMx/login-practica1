<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Rules\RecaptchaRule;
use App\Models\LoginLog;

/**
 * Controlador de autenticación principal.
 *
 * Gestiona el inicio y cierre de sesión de usuarios, incluyendo
 * validación de reCAPTCHA, registro de auditoría en LoginLog y
 * redirección condicional según el rol del usuario autenticado.
 *
 * @package App\Http\Controllers\Auth
 */
class LoginController extends Controller
{
    /**
     * Muestra el formulario de inicio de sesión.
     *
     * @return \Illuminate\View\View  Vista 'auth.login'
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Procesa el intento de inicio de sesión del usuario.
     *
     * Valida las credenciales junto con el token reCAPTCHA. Si la
     * autenticación es exitosa, regenera la sesión y redirige al usuario
     * según su rol:
     *
     * - `invitado`      → dashboard de invitado directamente.
     * - `usuario`       → flujo de verificación 2FA.
     * - `administrador` → flujo de verificación 2FA.
     *
     * En caso de fallo, registra el intento en LoginLog y lanza una
     * excepción de validación con el mensaje de error estándar de Laravel.
     *
     * @param  \Illuminate\Http\Request  $request  Datos del formulario:
     *                                             - string $email
     *                                             - string $password
     *                                             - string $g-recaptcha-response
     *                                             - bool   $remember  (opcional)
     *
     * @return \Illuminate\Http\RedirectResponse  Redirección según rol o de vuelta al login
     *
     * @throws \Illuminate\Validation\ValidationException  Si las credenciales son incorrectas
     *                                                     o el reCAPTCHA falla
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'               => ['required', 'email'],
            'password'            => ['required', 'string'],
            'g-recaptcha-response' => ['required', new RecaptchaRule],
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Registrar intento exitoso
            LoginLog::create([
                'email'      => $request->email,
                'ip'         => $request->ip(),
                'exitoso'    => true,
                'user_agent' => $request->userAgent(),
            ]);

            $user = Auth::user();
            $role = $user->getRoleNames()->first();

            if ($role === 'invitado') {
                LoginLog::create([
                    'email'      => $request->email,
                    'ip'         => $request->ip(),
                    'exitoso'    => true,
                    'user_agent' => $request->userAgent(),
                ]);
                return redirect()->route('dashboard.invitado')->with('success', '¡Bienvenido!');
            } elseif ($role === 'usuario') {
                session(['auth.2fa.pending' => true]);
                return redirect()->route('2fa.verify');
            } elseif ($role === 'administrador') {
                session(['auth.2fa.pending' => true]);
                return redirect()->route('2fa.verify');
            }
        }

        // Registrar intento fallido
        LoginLog::create([
            'email'      => $request->email,
            'ip'         => $request->ip(),
            'error_en'  => 'Error al validar credenciales',
            'exitoso'    => false,
            'user_agent' => $request->userAgent(),
        ]);

        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    /**
     * Cierra la sesión del usuario autenticado.
     *
     * Invalida la sesión actual, regenera el token CSRF y redirige
     * al formulario de login con un mensaje de confirmación.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\RedirectResponse  Redirección a la ruta 'login'
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('message', 'Sesión cerrada correctamente');
    }
}