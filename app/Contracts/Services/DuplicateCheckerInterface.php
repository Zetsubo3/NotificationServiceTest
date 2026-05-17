<?php

namespace App\Contracts\Services;

interface DuplicateCheckerInterface
{
    /**
     * Проверить, не отправлялось ли уже такое сообщение
     */
    public function isDuplicate(int $recipientId, string $channel, string $message, string $priority): bool;

    /**
     * Отметить сообщение как отправленное
     */
    public function markAsSent(int $recipientId, string $channel, string $message, string $priority, int $ttl = 60): void;
}
