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

    /**
     * Увеличить счётчик попыток уведомления
     *
     * @param int $notificationId
     * @return void
     */
    public function incrementAttempts(int $notificationId): void;

    /**
     * Отметить уведомление как отправленное (sent)
     *
     * @param int $notificationId
     * @param string $externalId
     * @param int $attempts
     * @return void
     */
    public function markAsSent(int $notificationId, string $externalId, int $attempts): void;

    /**
     * Отметить уведомление как доставленное (delivered)
     *
     * @param string $externalId
     * @param string $deliveredAt
     * @return void
     */
    public function markAsDelivered(string $externalId, string $deliveredAt): void;

    /**
     * Отметить уведомление как не доставленное (failed)
     *
     * @param string $externalId
     * @param string $error
     * @return void
     */
    public function markDeliveryFailed(string $externalId, string $error): void;

    /**
     * Отметить уведомление как окончательно проваленное (failed)
     *
     * @param int $notificationId
     * @param string $error
     * @param int $totalAttempts
     * @return void
     */
    public function markAsFailed(int $notificationId, string $error, int $totalAttempts): void;
}
