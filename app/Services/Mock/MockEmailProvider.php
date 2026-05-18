<?php

namespace App\Services\Mock;

use App\Contracts\Services\NotificationProviderInterface;
use Exception;
use Illuminate\Support\Str;

class MockEmailProvider implements NotificationProviderInterface
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
                'error' => 'Provider temporary unavailable',
                'permanent' => false
            ];
        }

        return [
            'success' => true,
            'external_id' => 'email_' . Str::uuid()->toString()
        ];
    }
}
