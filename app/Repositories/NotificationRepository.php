<?php

namespace App\Repositories;

use App\Contracts\Repositories\NotificationRepositoryInterface;
use App\DTO\NotificationDTO;
use App\DTO\NotificationStatusLogDTO;
use App\DTO\PaginatedResponseDTO;
use App\DTO\PaginationDTO;
use App\Filters\NotificationFilter;
use App\Models\Notification;
use App\Models\NotificationStatusLog;

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

            // select для получения всех созданных уведомлений
            $models = Notification::query()
                ->whereIn('recipient_id', $chunk)
                ->where('created_at', $now)
                ->get();

            // готовим данные для логов
            $statusLogsData = [];
            foreach ($models as $model) {
                $statusLogsData[] = [
                    'notification_id' => $model->id,
                    'status' => Notification::STATUS_QUEUED,
                    'metadata' => json_encode(['queue' => $priority === 'high' ? 'transactional' : 'marketing']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            NotificationStatusLog::query()->insert($statusLogsData);

            foreach ($models as $model) {
                $statusLogDTOs = [
                    NotificationStatusLogDTO::fromArray([
                        'status' => Notification::STATUS_QUEUED,
                        'metadata' => ['queue' => $priority === 'high' ? 'transactional' : 'marketing'],
                        'created_at' => $now->toISOString(),
                    ])
                ];

                $notificationData = [
                    'id' => $model->id,
                    'recipient_id' => $model->recipient_id,
                    'channel' => $model->channel,
                    'priority' => $model->priority,
                    'message' => $model->message,
                    'status' => $model->status,
                    'external_id' => $model->external_id,
                    'attempts' => $model->attempts,
                    'created_at' => $model->created_at->toISOString(),
                    'updated_at' => $model->updated_at->toISOString(),
                ];

                $allNotifications[] = NotificationDTO::fromArray($notificationData, $statusLogDTOs);
            }
        }

        return $allNotifications;
    }

    public function getSubscriberNotifications(int $recipientId, array $filterParams): PaginatedResponseDTO
    {
        // подгружаем статус логи
        $query = Notification::with(['statusLogs' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->where('recipient_id', $recipientId);

        $query = $this->notificationFilter->apply($query, $filterParams);

        $perPage = $filterParams['count'] ?? 15;
        $page = $filterParams['page'] ?? 1;

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

    public function incrementAttempts(int $notificationId): void
    {
        Notification::query()->where('id', $notificationId)->increment('attempts');
    }

    public function markAsSent(int $notificationId, string $externalId, int $attempts): void
    {
        $notification = Notification::query()->find($notificationId);

        if (!$notification) {
            return;
        }

        $notification->update([
            'status' => Notification::STATUS_SENT,
            'external_id' => $externalId,
        ]);

        NotificationStatusLog::query()->create([
            'notification_id' => $notification->id,
            'status' => Notification::STATUS_SENT,
            'metadata' => [
                'external_id' => $externalId,
                'attempts' => $attempts
            ],
        ]);
    }

    public function markAsDelivered(string $externalId, string $deliveredAt): void
    {
        $notification = Notification::query()->where('external_id', $externalId)->first();

        if (!$notification) {
            return;
        }

        $notification->update([
            'status' => Notification::STATUS_DELIVERED,
        ]);

        NotificationStatusLog::query()->create([
            'notification_id' => $notification->id,
            'status' => Notification::STATUS_DELIVERED,
            'metadata' => ['delivered_at' => $deliveredAt],
        ]);
    }

    public function markDeliveryFailed(string $externalId, string $error): void
    {
        $notification = Notification::query()->where('external_id', $externalId)->first();

        if (!$notification) {
            return;
        }

        $notification->update([
            'status' => Notification::STATUS_FAILED,
        ]);

        NotificationStatusLog::query()->create([
            'notification_id' => $notification->id,
            'status' => Notification::STATUS_FAILED,
            'metadata' => [
                'error' => $error,
                'source' => 'delivery_callback'
            ],
        ]);
    }

    public function markAsFailed(int $notificationId, string $error, int $totalAttempts): void
    {
        $notification = Notification::query()->find($notificationId);

        if (!$notification) {
            return;
        }

        $notification->update([
            'status' => Notification::STATUS_FAILED,
        ]);

        NotificationStatusLog::query()->create([
            'notification_id' => $notification->id,
            'status' => Notification::STATUS_FAILED,
            'metadata' => [
                'error' => $error,
                'total_attempts' => $totalAttempts,
                'source' => 'job_failed'
            ],
        ]);
    }

    /**
     * Маппинг модели в DTO
     *
     * @param Notification $notification
     * @return NotificationDTO
     */
    private function mapToDTO(Notification $notification): NotificationDTO
    {
        //крутим сатус лог в ДТО
        $statusLogs = $notification->statusLogs->map(
            fn($log) => NotificationStatusLogDTO::fromArray([
                'status' => $log->status,
                'metadata' => $log->metadata,
                'created_at' => $log->created_at->toISOString(),
            ])
        )->toArray();

        return NotificationDTO::fromArray(
            data: [
                'id' => $notification->id,
                'recipient_id' => $notification->recipient_id,
                'channel' => $notification->channel,
                'priority' => $notification->priority,
                'message' => $notification->message,
                'status' => $notification->status,
                'external_id' => $notification->external_id,
                'attempts' => $notification->attempts,
                'created_at' => $notification->created_at->toISOString(),
                'updated_at' => $notification->updated_at->toISOString(),
            ],
            statusLogs: $statusLogs
        );
    }
}
