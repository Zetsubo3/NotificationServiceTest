<?php

namespace App\Contracts\Services;

use App\DTO\NotificationDTO;

interface QueueDispatcherServiceInterface
{
    /**
     * Отправить одно уведомление в очередь
     *
     * @param NotificationDTO $notification
     * @param string $priority
     * @return void
     */
    public function dispatch(NotificationDTO $notification, string $priority): void;

    /**
     * Отправить несколько уведомлений в очередь
     *
     * @param array<NotificationDTO> $notifications
     * @param string $priority
     * @return void
     */
    public function dispatchBatch(array $notifications, string $priority): void;
}
