<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notification extends Model
{
    protected $fillable = [
        'recipient_id',
        'channel',
        'priority',
        'message',
        'status',
        'external_id',
        'attempts',
    ];

    protected $casts = [
        'attempts' => 'integer',
    ];

    /**
     * Статусы
     */
    public const STATUS_QUEUED = 'queued';
    public const STATUS_SENT = 'sent';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_FAILED = 'failed';

    /**
     * Приоритетность
     */
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_LOW = 'low';

    /**
     * Каналы связи
     */
    public const CHANNEL_SMS = 'sms';
    public const CHANNEL_EMAIL = 'email';

    /**
     * Получатель сообщения
     *
     * @return BelongsTo
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(Recipient::class);
    }

    /**
     * История изменений сообщения
     *
     * @return HasMany
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(NotificationStatusLog::class);
    }
}
