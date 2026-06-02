<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ThreeFactorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Esta capa solo aplica para administradores
        $this->middleware(function ($request, $next) {
            /** @var User|null $user */
            $user = Auth::user();
            if ($user->getRoleNames()->first() !== 'administrador') {
                return redirect('/dashboard');
            }
            return $next($request);
        });
    }

    public function showVerifyForm()
    {
        // Si el código ya se verificó, redirigir
        if (session('auth.3fa.completed')) {
            return redirect()->intended('/dashboard');
        }

        // Generar y enviar un nuevo código si no existe en sesión
        if (!session()->has('auth.3fa.code')) {
            $this->generateAndSendCode();
        }

        return view('auth.3fa_verify');
    }

    protected function generateAndSendCode()
    {
        $code = Str::random(6); // Código alfanumérico de 6 caracteres
        $user = Auth::user();

        // Guardar en sesión con expiración (ej: 10 minutos)
        session([
            'auth.3fa.code' => $code,
            'auth.3fa.expires_at' => now()->addMinutes(10)
        ]);

        // Enviar email
        Mail::send('emails.3fa_code', ['code' => $code, 'user' => $user], function ($message) use ($user) {
            $message->to($user->email, $user->name)
                    ->subject('Código de Verificación de Tres Factores');
        });
    }

    public function verify(Request $request)
    {
        $request->validate(['verification_code' => 'required|string']);

        if (!session()->has('auth.3fa.code') || now()->gt(session('auth.3fa.expires_at'))) {
            return back()->withErrors(['verification_code' => 'El código ha expirado. Se ha enviado uno nuevo.'])->with('resend', true);
        }

        if ($request->verification_code !== session('auth.3fa.code')) {
            return back()->withErrors(['verification_code' => 'El código ingresado es incorrecto.']);
        }

        // Limpiar sesión y marcar como completado
        session()->forget(['auth.2fa.pending', 'auth.3fa.code', 'auth.3fa.expires_at']);
        session(['auth.3fa.completed' => true]);

        return redirect()->intended('/dashboard')->with('success', '¡Bienvenido, Administrador!');
    }

    public function resendCode()
    {
        session()->forget(['auth.3fa.code', 'auth.3fa.expires_at']);
        $this->generateAndSendCode();
        return back()->with('status', 'Se ha enviado un nuevo código a tu correo electrónico.');
    }
}