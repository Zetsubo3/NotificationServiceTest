<?php

namespace App\Services\Queue;

use App\Contracts\Services\QueueDispatcherServiceInterface;
use App\DTO\NotificationDTO;
use App\Jobs\SendMarketingNotificationJob;
use App\Jobs\SendTransactionalNotificationJob;

class RabbitMQDispatcher implements QueueDispatcherServiceInterface
{
    public function dispatch(NotificationDTO $notification, string $priority): void
    {
        if ($priority === 'high') {
            SendTransactionalNotificationJob::dispatch($notification);
        } else {
            SendMarketingNotificationJob::dispatch($notification);
        }
    }

    public function dispatchBatch(array $notifications, string $priority): void
    {
        foreach ($notifications as $notification) {
            $this->dispatch($notification, $priority);
        }
    }
}
