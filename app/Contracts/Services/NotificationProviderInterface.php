<?php

namespace App\Contracts\Services;

interface NotificationProviderInterface
{
    /**
     * Отправить уведомление
     *
     * @param string $recipient
     * @param string $message
     * @return array{success: bool, external_id?: string, error?: string}
     */
    public function send(string $recipient, string $message): array;
}
