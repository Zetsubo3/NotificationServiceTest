<?php

namespace App\Jobs;

use App\Contracts\Repositories\NotificationRepositoryInterface;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Джоб для имитации асинхронного callback от провайдера
 */
class SimulateDeliveryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    /**
     * Имя очереди
     *
     * @var string
     */
    public string $queue = 'delivery';

    /**
     * Внешний айди уведомления от провайдера
     *
     * @var string
     */
    protected string $externalId;

    /**
     * Создание нового джоба
     *
     * @param string $externalId
     */
    public function __construct(string $externalId)
    {
        $this->externalId = $externalId;
    }

    /**
     * Выполнение джоба.
     *
     * @param NotificationRepositoryInterface $notificationRepository
     * @return void
     * @throws Exception
     */
    public function handle(NotificationRepositoryInterface $notificationRepository): void
    {
        Log::info('Simulating delivery callback', [
            'external_id' => $this->externalId
        ]);

        $successRate = config('mock.successful_callback_conversion_percentage', 70);
        $isDelivered = random_int(1, 100) <= $successRate;

        if ($isDelivered) {
            $notificationRepository->markAsDelivered(
                $this->externalId,
                now()->toISOString()
            );

            Log::info('Delivery successful', [
                'external_id' => $this->externalId
            ]);
        } else {
            $notificationRepository->markDeliveryFailed(
                $this->externalId,
                'Provider could not deliver the message'
            );

            Log::warning('Delivery failed', [
                'external_id' => $this->externalId
            ]);
        }
    }
}
