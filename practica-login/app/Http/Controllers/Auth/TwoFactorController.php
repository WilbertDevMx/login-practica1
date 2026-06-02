<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FAQRCode\Google2FA;
use App\Models\User;
use App\Models\TwoFactorLog;

class TwoFactorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // Requiere que el usuario esté logueado parcialmente
    }

    public function showVerifyForm(Request $request)
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
            $user->google2fa_secret = $secret;
            $user->save();
            // ... después de $user->save();
            \App\Models\TwoFactorLog::create([
                'user_id'    => $user->id,
                'email'      => $user->email,
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
                'action'     => 'setup',
                'successful' => true,
                'message'    => '2FA secret generated and QR shown',
            ]);

            $qrCodeUrl = $google2fa->getQRCodeInline(
                config('app.name'),
                $user->email,
                $secret
            );
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
        /** @var User|null $user */
        $user = Auth::user();
        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($user->google2fa_secret, $request->one_time_password);

        // Registrar el intento
        \App\Models\TwoFactorLog::create([
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

        // Marcar la verificación 2FA como completada
        session(['auth.2fa.completed' => true]);
        session()->forget('auth.2fa.pending');

        // Redirigir según el rol para la tercera fase
        $role = $user->getRoleNames()->first();
        if ($role === 'administrador') {
            return redirect()->route('3fa.verify'); // 3. IR AL 3FA (SOLO ADMIN)
        }

        // Es usuario estándar: login completo
        return redirect()->intended('/dashboard')->with('success', '¡Bienvenido!');
    }
}