<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Rules\RecaptchaRule;
use App\Models\LoginLog;

class LoginController extends Controller
{
    // Mostrar el formulario de login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Procesar el login
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'g-recaptcha-response' => ['required', new RecaptchaRule],
        ]);
        
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            /** @var User|null $user */
            $user = Auth::user();
            $role = $user->getRoleNames()->first(); // Obtiene el primer rol del usuario

            // Redirigir según el rol
            if ($role === 'invitado') {
                // 1. LOGIN EXITOSO PARA INVITADO
                return redirect()->intended('/dashboard')->with('success', '¡Bienvenido!');
            }
            elseif ($role === 'usuario') {
                // 2. LOGIN PARCIAL: Redirigir a verificación 2FA
                // Guardar en sesión que el usuario pasó la primera fase
                session(['auth.2fa.pending' => true]);
                return redirect()->route('2fa.verify');
            }
            elseif ($role === 'administrador') {
                // 3. LOGIN PARCIAL: Redirigir a verificación 2FA primero
                session(['auth.2fa.pending' => true]);
                return redirect()->route('2fa.verify');
            }
        }

        LoginLog::create([
            'email'      => $request->email,
            'ip'         => $request->ip(),
            'exitoso'    => false,
            'user_agent' => $request->userAgent(),
        ]);

        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    // Cerrar sesión
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('message', 'Sesión cerrada correctamente');
    }
}