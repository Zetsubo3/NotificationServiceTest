<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationStatusLog extends Model
{
    protected $table = 'notification_status_logs';
    protected $fillable = [
        'notification_id',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Уведомление
     *
     * @return BelongsTo
     */
    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }
}
