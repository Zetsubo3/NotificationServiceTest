<?php

namespace App\Services;

use App\Contracts\Repositories\NotificationRepositoryInterface;
use App\Contracts\Repositories\RecipientRepositoryInterface;
use App\Contracts\Services\DuplicateCheckerInterface;
use App\Contracts\Services\NotificationServiceInterface;
use App\Contracts\Services\QueueDispatcherServiceInterface;
use App\DTO\PaginatedResponseDTO;
use App\DTO\SendNotificationDTO;
use App\Exceptions\Recipients\DuplicateRecipientsException;
use App\Exceptions\Recipients\NoValidRecipientsException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotificationService implements NotificationServiceInterface
{
    public function __construct(
        protected readonly NotificationRepositoryInterface $notificationRepository,
        protected readonly RecipientRepositoryInterface $recipientRepository,
        protected readonly DuplicateCheckerInterface $duplicateChecker,
        protected readonly QueueDispatcherServiceInterface $queueDispatcher,
    ) {}

    public function send(SendNotificationDTO $dto): array
    {
        // убираем дубли в текущем запросе
        $uniqueIds = array_map('intval', array_unique($dto->recipientIds));
        // поверяем существование получателей
        $existingIds = $this->recipientRepository->findExistingIds($uniqueIds);
        $invalidIds = array_values(array_diff($uniqueIds, $existingIds));

        // если все айди невалидны - ошибка
        if (empty($existingIds)) {
            throw new NoValidRecipientsException($invalidIds);
        }

        // проверяем дубли в редисе (только для существующих)
        $duplicateIds = [];
        $validIds = [];

        foreach ($existingIds as $recipientId) {
            if ($this->duplicateChecker->isDuplicate($recipientId, $dto->channel, $dto->message, $dto->priority)) {
                $duplicateIds[] = $recipientId;
            } else {
                $validIds[] = $recipientId;
            }
        }

        // если нет ни одного нового получателя (все дубликаты) - ошибка
        if (empty($validIds)) {
            throw new DuplicateRecipientsException($duplicateIds);
        }

        $notifications = $this->notificationRepository->createBatch(
            recipientIds: $validIds,
            channel: $dto->channel,
            priority: $dto->priority,
            message: $dto->message
        );

        // отмечаем
        foreach ($validIds as $recipientId) {
            $this->duplicateChecker->markAsSent($recipientId, $dto->channel, $dto->message, $dto->priority);
        }

        // отправляем в джобы
        $this->queueDispatcher->dispatchBatch($notifications, $dto->priority);

        return [
            'total_requested' => count($dto->recipientIds),
            'total_invalid' => count($invalidIds),
            'total_duplicates' => count($duplicateIds),
            'total_queued' => count($notifications),
            'invalid_recipient_ids' => $invalidIds,
            'duplicate_recipient_ids' => $duplicateIds,
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
