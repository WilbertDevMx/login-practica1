<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para registrar eventos de verificación en dos pasos (2FA).
 *
 * @property int $id
 * @property int $user_id
 * @property string $email
 * @property string $ip
 * @property string|null $user_agent
 * @property string $action          // Ej: 'request', 'verify', 'disable'
 * @property bool $successful
 * @property string|null $message
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class TwoFactorLog extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'email',
        'ip',
        'user_agent',
        'action',
        'successful',
        'message',
    ];
}