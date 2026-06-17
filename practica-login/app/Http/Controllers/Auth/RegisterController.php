<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\RegistrationLog;
use App\Rules\RecaptchaRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Controlador de registro de nuevos usuarios.
 *
 * Gestiona el formulario de registro y la creación de cuentas, incluyendo
 * validación de campos con mensajes personalizados, verificación reCAPTCHA,
 * asignación de rol por defecto y auditoría completa en RegistrationLog
 * tanto para intentos fallidos como exitosos.
 *
 * @package App\Http\Controllers\Auth
 */
class RegisterController extends Controller
{
    /**
     * Muestra el formulario de registro de nuevos usuarios.
     *
     * @return \Illuminate\View\View  Vista 'auth.register'
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Procesa el registro de un nuevo usuario en el sistema.
     *
     * Ejecuta las siguientes acciones en orden:
     * 1. Valida los campos del formulario con reglas y mensajes personalizados.
     * 2. Si la validación falla, registra el intento en RegistrationLog y
     *    redirige de vuelta con los errores.
     * 3. Crea el usuario con la contraseña hasheada mediante bcrypt.
     * 4. Asigna el rol `invitado` por defecto (Spatie Permission).
     * 5. Registra el evento exitoso en RegistrationLog.
     * 6. Redirige al login con mensaje de confirmación.
     *
     * Reglas de validación aplicadas:
     * - `name`                 : requerido, string, máx. 100 caracteres.
     * - `email`                : requerido, formato email, máx. 255, único en tabla `users`.
     * - `password`             : requerido, mín. 12 / máx. 128 caracteres, confirmado,
     *                            debe contener mayúsculas, minúsculas, dígitos y símbolo (@$!%*?&_-#).
     * - `g-recaptcha-response` : requerido, validado mediante {@see RecaptchaRule}.
     *
     * @param  \Illuminate\Http\Request  $request  Datos del formulario:
     *                                             - string $name
     *                                             - string $email
     *                                             - string $password
     *                                             - string $password_confirmation
     *                                             - string $g-recaptcha-response
     *
     * @return \Illuminate\Http\RedirectResponse  Redirección al login si el registro es exitoso,
     *                                            o de vuelta al formulario si la validación falla
     */
    public function register(Request $request)
    {
        $validator = validator($request->all(), [
            'name'                  => ['required', 'string', 'max:100'],
            'email'                 => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'              => [
                'required',
                'string',
                'min:12',
                'max:128',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d])[^\s]+$/',
            ],
            'g-recaptcha-response'  => ['required', new RecaptchaRule],
        ], [
            'name.required'         => 'El nombre es obligatorio.',
            'name.max'              => 'El nombre no puede superar 100 caracteres.',
            'email.required'        => 'El correo es obligatorio.',
            'email.email'           => 'El correo no tiene un formato válido.',
            'email.unique'          => 'Este correo ya está registrado.',
            'password.required'     => 'La contraseña es obligatoria.',
            'password.min'          => 'La contraseña debe tener al menos 12 caracteres.',
            'password.confirmed'    => 'Las contraseñas no coinciden.',
            'password.regex'        => 'La contraseña debe incluir mayúsculas, minúsculas, números y un símbolo (@$!%*?&_-#).',
        ]);

        // Log de intento fallido (si falla la validación)
        if ($validator->fails()) {
            RegistrationLog::create([
                'email'      => $request->email,
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
                'successful' => false,
                'message'    => 'Error de validación: ' . implode('; ', $validator->errors()->all()),
            ]);
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Crear usuario
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Asignar rol por defecto
        $user->assignRole('invitado');

        // Log de registro exitoso
        RegistrationLog::create([
            'user_id'    => $user->id,
            'email'      => $user->email,
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
            'successful' => true,
            'message'    => 'Registro exitoso',
        ]);

        return redirect()->route('login')->with('message', '¡Cuenta creada exitosamente! Inicia sesión.');
    }
}