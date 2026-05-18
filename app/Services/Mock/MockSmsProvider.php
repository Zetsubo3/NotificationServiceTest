<?php

namespace App\Services\Mock;

use App\Contracts\Services\NotificationProviderInterface;
use Exception;
use Illuminate\Support\Str;

class MockSmsProvider implements NotificationProviderInterface
{
    /**
     * @throws Exception
     */
    public function send(string $recipient, string $message): array
    {
        $successRate = config('mock.successful_admission_percentage', 70);

        if (random_int(1, 100) >= $successRate) {

            return [
                'success' => false,
                'error' => 'SMS provider temporary unavailable',
                'permanent' => false
            ];
        }

        $externalId = 'sms_' . Str::uuid()->toString();

        return [
            'success' => true,
            'external_id' => $externalId
        ];
    }
}
