<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TwoFactorLog extends Model
{
    use HasFactory;

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
