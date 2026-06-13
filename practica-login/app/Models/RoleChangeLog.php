<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleChangeLog extends Model
{
    protected $fillable = [
        'admin_id',
        'admin_email',
        'target_user_id',
        'target_email',
        'rol_anterior',
        'rol_nuevo',
        'ip',
    ];
}