<?php

namespace App\Http\Middleware;

use Closure;

class SecureHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->remove('X-Powered-By');
        $response->headers->set('Server', '');
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; " .
            "style-src 'self' 'unsafe-inline'; " .
            "script-src 'self' 'unsafe-inline' https://www.google.com/recaptcha/ https://www.gstatic.com/recaptcha/; " .
            "frame-src https://www.google.com/recaptcha/; " .
            "img-src 'self' data:;"
        );

        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}