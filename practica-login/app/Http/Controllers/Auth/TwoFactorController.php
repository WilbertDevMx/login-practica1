<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FAQRCode\Google2FA;
use App\Models\User;

class TwoFactorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // Requiere que el usuario esté logueado parcialmente
    }

    public function showVerifyForm()
    {
        // Si el usuario ya completó el 2FA, redirigir
        if (session('auth.2fa.completed')) {
            return redirect()->intended('/dashboard');
        }
        /** @var User|null $user */
        $user = Auth::user();
        $google2fa = new Google2FA();

        // Si el usuario no tiene un secreto, se lo generamos y mostramos el QR
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
                'isNew' => true
            ]);
        }

        // Si ya tiene un secreto, solo mostramos el formulario de verificación
        return view('auth.2fa_verify', [
            'isNew' => false
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate(['one_time_password' => 'required|string']);

        $user = Auth::user();
        $google2fa = new Google2FA();

        // Si está en proceso de setup, usar el secret de sesión
        $secret = $user->google2fa_secret ?? session('auth.2fa.setup_secret');

        if (empty($secret)) {
            return back()->withErrors(['one_time_password' => 'No hay secret configurado. Inicia sesión de nuevo.']);
        }

        $valid = $google2fa->verifyKey($secret, $request->one_time_password);

        if (!$valid) {
            return back()->withErrors(['one_time_password' => 'El código de verificación no es válido.']);
        }

        // Solo aquí persistir el secret si era nuevo
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

        // Log exitoso para usuario
        \App\Models\LoginLog::create([
            'email'      => $user->email,
            'ip'         => $request->ip(),
            'exitoso'    => true,
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->intended('/dashboard')->with('success', '¡Bienvenido!');
    }
}