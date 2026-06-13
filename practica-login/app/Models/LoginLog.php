<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    protected $fillable = ['email', 'ip', 'exitoso', 'user_agent'];
}
