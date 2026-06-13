<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationLog extends Model
{
    protected $fillable = ['user_id', 'email', 'ip', 'user_agent', 'successful', 'message'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
