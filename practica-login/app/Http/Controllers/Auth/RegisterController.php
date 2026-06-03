<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\RegistrationLog;
use App\Rules\RecaptchaRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.register');
    }

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
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&_\-#])[A-Za-z\d@$!%*?&_\-#]+$/',
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