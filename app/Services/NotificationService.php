<?php

namespace App\Services;

use App\Contracts\Repositories\NotificationRepositoryInterface;
use App\Contracts\Repositories\RecipientRepositoryInterface;
use App\Contracts\Services\NotificationServiceInterface;
use App\DTO\PaginatedResponseDTO;
use App\DTO\SendNotificationDTO;
use App\Exceptions\Recipients\NoValidRecipientsException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotificationService implements NotificationServiceInterface
{
    public function __construct(
        protected readonly NotificationRepositoryInterface $notificationRepository,
        protected readonly RecipientRepositoryInterface $recipientRepository,
    ) {}

    public function send(SendNotificationDTO $dto): array
    {
        // проверяем существование получателей
        $uniqueIds = array_unique($dto->recipientIds);
        $existingIds = $this->recipientRepository->findExistingIds($uniqueIds);
        $invalidIds = array_values(array_diff($uniqueIds, $existingIds));

        if (empty($existingIds)) {
            throw new NoValidRecipientsException($invalidIds);
        }

        $invalidIds = array_map('intval', $invalidIds);
        $existingIds = array_map('intval', $existingIds);

        // создаём уведомления только для существующих получателей
        $totalQueued = 0;

        if (!empty($existingIds)) {
            $notifications = $this->notificationRepository->createBatch(
                recipientIds: $existingIds,
                channel: $dto->channel,
                priority: $dto->priority,
                message: $dto->message
            );

            $totalQueued = count($notifications);
        }

        return [
            'total_requested' => count($dto->recipientIds),
            'total_invalid' => count($invalidIds),
            'total_queued' => $totalQueued,
            'invalid_recipient_ids' => $invalidIds,
        ];
    }

    public function getSubscriberNotifications(int $recipientId, array $filterParams): PaginatedResponseDTO
    {
        $recipient = $this->recipientRepository->findById($recipientId);

        if (!$recipient) {
            throw new NotFoundHttpException('Recipient not found');
        }

        return $this->notificationRepository->getSubscriberNotifications($recipientId, $filterParams);
    }
}
