<?php

namespace App\Http\Controllers\API;

use App\Contracts\Services\NotificationServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Notifications\SendRequest;
use App\Http\Requests\Notifications\SubscriberNotificationsRequest;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function __construct(
        protected readonly NotificationServiceInterface $notificationService
    ) {}

    /**
     * Ендпоинт в метод получения истории уведомлений подписчика
     *
     * @param int $recipientId
     * @param SubscriberNotificationsRequest $request
     * @return JsonResponse
     */
    public function history(int $recipientId, SubscriberNotificationsRequest $request): JsonResponse
    {
        $filterParams = $request->getRequestParams();
        $paginatedDTO = $this->notificationService->getSubscriberNotifications($recipientId, $filterParams);

        return $this->formatResponse(
            data: $paginatedDTO->toArray(),
            message: 'Notifications history retrieved successfully',
        );
    }

    /**
     * Ендпоинт в метод отправки уведомления
     *
     * @param SendRequest $request
     * @return JsonResponse
     */
    public function send(SendRequest $request): JsonResponse
    {
        $dto = $request->toDTO();
        $result = $this->notificationService->send($dto);

        return $this->formatResponse(
            data: $result,
            message: 'Notifications have been queued successfully',
            statusCode: 202
        );
    }
}
