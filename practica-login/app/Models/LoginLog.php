<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para registrar intentos de inicio de sesión.
 *
 * @property int $id
 * @property string $email
 * @property string $ip
 * @property bool $exitoso
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class LoginLog extends Model
{
    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'ip',
        'error_en',
        'exitoso',
        'user_agent',
    ];
}