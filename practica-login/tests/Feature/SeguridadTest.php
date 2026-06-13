<?php

namespace Tests\Feature;

use App\Http\Middleware\SecureHeaders;
use App\Rules\RecaptchaRule;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SeguridadTest extends TestCase
{
    // --- SecureHeaders ---

    protected function setUp(): void
    {
        parent::setUp();

        // Ruta de prueba con el middleware aplicado
        Route::get('/_test/secure', function () {
            return response('ok');
        })->middleware(SecureHeaders::class);
    }

    public function test_header_x_frame_options_es_deny(): void
    {
        $response = $this->get('/_test/secure');
        $response->assertHeader('X-Frame-Options', 'DENY');
    }

    public function test_header_x_content_type_options_es_nosniff(): void
    {
        $response = $this->get('/_test/secure');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_header_x_xss_protection_esta_presente(): void
    {
        $response = $this->get('/_test/secure');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
    }

    public function test_header_referrer_policy_esta_presente(): void
    {
        $response = $this->get('/_test/secure');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_header_content_security_policy_incluye_self(): void
    {
        $response = $this->get('/_test/secure');

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'self'", $csp);
    }

    public function test_header_csp_permite_recaptcha(): void
    {
        $response = $this->get('/_test/secure');

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString('https://www.google.com/recaptcha/', $csp);
        $this->assertStringContainsString('https://www.gstatic.com/recaptcha/', $csp);
    }

    public function test_header_csp_permite_data_uri_para_qr(): void
    {
        $response = $this->get('/_test/secure');

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("img-src 'self' data:", $csp);
    }

    public function test_header_server_es_vaciado(): void
    {
        $response = $this->get('/_test/secure');
        $response->assertHeader('Server', '');
    }

    public function test_hsts_no_se_aplica_en_entorno_testing(): void
    {
        // HSTS solo se activa en producción — en testing no debe estar presente
        $response = $this->get('/_test/secure');
        $this->assertNull($response->headers->get('Strict-Transport-Security'));
    }

    // --- RecaptchaRule ---

    public function test_recaptcha_pasa_con_respuesta_exitosa_de_google(): void
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
            ]),
        ]);

        $rule = new RecaptchaRule();
        $this->assertTrue($rule->passes('g-recaptcha-response', 'token-valido'));
    }

    public function test_recaptcha_falla_con_respuesta_fallida_de_google(): void
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => false,
            ]),
        ]);

        $rule = new RecaptchaRule();
        $this->assertFalse($rule->passes('g-recaptcha-response', 'token-invalido'));
    }

    public function test_recaptcha_falla_si_google_no_retorna_campo_success(): void
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'error-codes' => ['invalid-input-response'],
            ]),
        ]);

        $rule = new RecaptchaRule();
        $this->assertFalse($rule->passes('g-recaptcha-response', 'token-roto'));
    }

    public function test_recaptcha_message_retorna_string_no_vacio(): void
    {
        $rule = new RecaptchaRule();
        $this->assertNotEmpty($rule->message());
    }
}
