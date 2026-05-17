<?php

namespace App\Contracts\Repositories;

use App\DTO\NotificationDTO;
use App\DTO\PaginatedResponseDTO;

interface NotificationRepositoryInterface
{
    /**
     * Массовое создание уведомлений
     *
     * @param array $recipientIds
     * @param string $channel
     * @param string $priority
     * @param string $message
     * @return array<NotificationDTO>
     */
    public function createBatch(
        array $recipientIds,
        string $channel,
        string $priority,
        string $message
    ): array;

    /**
     * Получить историю и статусы уведомлений подписчика
     *
     * @param int $recipientId
     * @param array $filterParams
     * @return PaginatedResponseDTO
     */
    public function getSubscriberNotifications(int $recipientId, array $filterParams): PaginatedResponseDTO;
}
