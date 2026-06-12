<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Http;

/**
 * Regla de validación que verifica reCAPTCHA v2/v3 con Google.
 *
 * Se usa en formularios para prevenir bots, validando el token
 * enviado desde el frontend contra la API de Google.
 */
class RecaptchaRule implements Rule
{
    /**
     * Determina si la validación pasa o falla.
     *
     * @param string $attribute El nombre del campo que se está validando.
     * @param mixed $value El valor del campo (token de reCAPTCHA).
     * @return bool True si el token es válido, false en caso contrario.
     */
    public function passes($attribute, $value): bool
    {
        $response = Http::withOptions([
            'verify' => false, // ⚠️ Desactiva verificación SSL (solo desarrollo)
        ])->asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('services.recaptcha.secret_key'),
            'response' => $value,
            'remoteip' => request()->ip(),
        ]);

        $body = $response->json();

        return isset($body['success']) && $body['success'] === true;
    }

    /**
     * Obtiene el mensaje de error de validación.
     *
     * @return string El mensaje mostrado cuando falla la validación.
     */
    public function message(): string
    {
        return '❌ Error de verificación reCAPTCHA. Por favor, inténtalo de nuevo.';
    }
}