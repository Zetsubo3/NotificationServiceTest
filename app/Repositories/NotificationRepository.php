<?php

namespace App\Repositories;

use App\Contracts\Repositories\NotificationRepositoryInterface;
use App\DTO\NotificationDTO;
use App\DTO\PaginatedResponseDTO;
use App\DTO\PaginationDTO;
use App\Filters\NotificationFilter;
use App\Models\Notification;

class NotificationRepository implements NotificationRepositoryInterface
{
    public function __construct(
        protected readonly NotificationFilter $notificationFilter
    ) {}

    public function createBatch(
        array $recipientIds,
        string $channel,
        string $priority,
        string $message
    ): array {
        if (empty($recipientIds)) {
            return [];
        }

        $now = now();
        $chunkSize = config('database.batch_chunk_size');
        $allNotifications = [];

        foreach (array_chunk($recipientIds, $chunkSize) as $chunk) {
            $notificationsData = array_map(fn($id) => [
                'recipient_id' => $id,
                'channel' => $channel,
                'priority' => $priority,
                'message' => $message,
                'status' => Notification::STATUS_QUEUED,
                'attempts' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ], $chunk);

            Notification::query()->insert($notificationsData);

            // получаем созданные записи для этого чанка
            $models = Notification::query()
                ->whereIn('recipient_id', $chunk)
                ->where('created_at', $now)
                ->get();

            $allNotifications = array_merge(
                $allNotifications,
                $models->map(fn($model) => NotificationDTO::fromModel($model))->toArray()
            );
        }

        return $allNotifications;
    }

    public function getSubscriberNotifications(int $recipientId, array $filterParams): PaginatedResponseDTO
    {
        $query = Notification::query()->where('recipient_id', $recipientId);

        $query = $this->notificationFilter->apply($query, $filterParams);

        $perPage = $filterParams['count'];
        $page = $filterParams['page'];

        $paginator = $query->paginate(perPage: $perPage, page: $page);

        $items = array_map(
            fn(Notification $notification) => $this->mapToDTO($notification),
            $paginator->items()
        );

        return new PaginatedResponseDTO(
            items: $items,
            pagination: PaginationDTO::fromPaginator($paginator)
        );
    }

    /**
     * Маппинг модели в DTO
     *
     * @param Notification $notification
     * @return NotificationDTO
     */
    private function mapToDTO(Notification $notification): NotificationDTO
    {
        return new NotificationDTO(
            id: $notification->id,
            recipientId: $notification->recipient_id,
            channel: $notification->channel,
            priority: $notification->priority,
            message: $notification->message,
            status: $notification->status,
            externalId: $notification->external_id,
            attempts: $notification->attempts,
            createdAt: $notification->created_at->toISOString(),
            updatedAt: $notification->updated_at->toISOString(),
        );
    }
}
