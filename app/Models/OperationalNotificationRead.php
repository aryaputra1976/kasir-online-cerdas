<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationalNotificationRead extends Model
{
    protected $fillable = [
        'user_id',
        'notification_key',
        'last_read_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'last_read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
