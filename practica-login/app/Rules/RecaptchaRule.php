<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Http;

class RecaptchaRule implements Rule
{
    public function passes($attribute, $value)
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

    public function message()
    {
        return '❌ Error de verificación reCAPTCHA. Por favor, inténtalo de nuevo.';
    }
}