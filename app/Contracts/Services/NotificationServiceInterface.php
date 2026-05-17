<?php

namespace App\Contracts\Services;

use App\DTO\NotificationDTO;
use App\DTO\PaginatedResponseDTO;
use App\DTO\SendNotificationDTO;

interface NotificationServiceInterface
{
    /**
     * Отправить уведомление (массовая рассылка)
     *
     * @param SendNotificationDTO $dto
     * @return array{
     *     total_requested: int,
     *     total_invalid: int,
     *     total_queued: int,
     *     invalid_recipient_ids: array<int>
     * }
     */
    public function send(SendNotificationDTO $dto): array;

    /**
     * Получить историю уведомлений подписчика
     *
     * @param int $recipientId
     * @param array $filterParams
     * @return PaginatedResponseDTO
     */
    public function getSubscriberNotifications(int $recipientId, array $filterParams): PaginatedResponseDTO;
}

