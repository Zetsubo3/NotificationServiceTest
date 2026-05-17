<?php

namespace App\DTO;

class SendNotificationDTO
{
    public function __construct(
        public readonly string $channel,
        public readonly string $message,
        public readonly array $recipientIds,
        public readonly string $priority = 'low',
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            channel: $data['channel'],
            message: $data['message'],
            recipientIds: $data['recipient_ids'],
            priority: $data['priority'] ?? 'low',
        );
    }

    public function toArray(): array
    {
        return [
            'channel' => $this->channel,
            'message' => $this->message,
            'recipient_ids' => $this->recipientIds,
            'priority' => $this->priority,
        ];
    }
}
