<?php

namespace App\Jobs;

use App\Contracts\Repositories\NotificationRepositoryInterface;
use App\DTO\NotificationDTO;
use App\Services\Mock\MockEmailProvider;
use App\Services\Mock\MockSmsProvider;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Джоб для отправки транзакционных уведомлений (высокий приоритет)
 */
class SendTransactionalNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    /**
     * Имя очереди для высокого приоритета
     *
     * @var string
     */
    public string $queue = 'high';

    /**
     * Максимальное количество попыток
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * Задержки между попытками
     *
     * @var array<int>
     */
    public array $backoff = [5, 15, 30];

    /**
     * DTO уведомления
     *
     * @var NotificationDTO
     */
    protected NotificationDTO $notification;

    /**
     * Создание джоба
     *
     * @param NotificationDTO $notification
     */
    public function __construct(NotificationDTO $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Выполнение джоба
     *
     * @param MockSmsProvider $smsProvider
     * @param MockEmailProvider $emailProvider
     * @param NotificationRepositoryInterface $notificationRepository
     * @return void
     * @throws Exception
     */
    public function handle(
        MockSmsProvider $smsProvider,
        MockEmailProvider $emailProvider,
        NotificationRepositoryInterface $notificationRepository
    ): void {
        Log::info('Transactional notification job started', [
            'notification_id' => $this->notification->id,
            'recipient_id' => $this->notification->recipientId,
            'channel' => $this->notification->channel,
            'attempt' => $this->attempts()
        ]);

        // Увеличиваем счётчик попыток через репозиторий
        $notificationRepository->incrementAttempts($this->notification->id);

        $provider = $this->notification->channel === 'sms' ? $smsProvider : $emailProvider;

        $result = $provider->send(
            recipient: (string) $this->notification->recipientId,
            message: $this->notification->message
        );

        // Временная ошибка - retry
        if (!$result['success']) {
            Log::warning('Provider error, scheduling retry', [
                'notification_id' => $this->notification->id,
                'attempt' => $this->attempts(),
                'remaining' => $this->tries - $this->attempts()
            ]);

            if ($this->attempts() < $this->tries) {
                $delay = $this->backoff[$this->attempts() - 1] ?? 30;
                $this->release($delay);
                return;
            }

            $this->fail(new Exception($result['error']));
            return;
        }

        // Успех - отмечаем как sent через репозиторий
        $notificationRepository->markAsSent(
            $this->notification->id,
            $result['external_id'],
            $this->attempts()
        );

        Log::info('Notification sent to provider', [
            'notification_id' => $this->notification->id,
            'external_id' => $result['external_id']
        ]);

        // Диспатчим джоб для имитации доставки
        $deliveryJob = new SimulateDeliveryJob($result['external_id']);
        dispatch($deliveryJob);
    }

    /**
     * Обработчик фэйла.
     *
     * @param Throwable $e
     * @param NotificationRepositoryInterface $notificationRepository
     * @return void
     */
    public function failed(
        Throwable $e,
        NotificationRepositoryInterface $notificationRepository
    ): void {
        Log::error('Transactional notification job failed after all retries', [
            'notification_id' => $this->notification->id,
            'recipient_id' => $this->notification->recipientId,
            'error' => $e->getMessage(),
            'total_attempts' => $this->tries
        ]);

        $notificationRepository->markAsFailed(
            $this->notification->id,
            $e->getMessage(),
            $this->tries
        );
    }
}
