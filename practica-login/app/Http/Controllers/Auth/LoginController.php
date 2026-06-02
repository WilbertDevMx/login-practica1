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
    public function showLoginForm()
    {
        return view('auth.login');
    }

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
                return redirect()->intended('/dashboard')->with('success', '¡Bienvenido!');
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
            'exitoso'    => false,
            'user_agent' => $request->userAgent(),
        ]);

        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('message', 'Sesión cerrada correctamente');
    }
}