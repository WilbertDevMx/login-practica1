<?php

namespace App\Helpers;

use Illuminate\Http\Request;

class IpHelper
{
    public static function ubicacion(Request $request): string
    {
        $ip = $request->ip();

        $esLocal = in_array($ip, ['127.0.0.1', '::1', 'localhost'])
                   || str_starts_with($ip, '192.168.')
                   || str_starts_with($ip, '10.')
                   || str_starts_with($ip, '172.');

        if ($esLocal) {
            return 'localhost (' . gethostname() . ')';
        }

        return $ip;
    }
}