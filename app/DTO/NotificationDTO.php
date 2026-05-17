<?php

namespace App\DTO;

use App\Models\Notification;

class NotificationDTO
{
    public function __construct(
        public readonly int $id,
        public readonly int $recipientId,
        public readonly string $channel,
        public readonly string $priority,
        public readonly string $message,
        public readonly string $status,
        public readonly ?string $externalId,
        public readonly int $attempts,
        public readonly string $createdAt,
        public readonly string $updatedAt,
        public readonly array $statusLogs = [],
    ) {}

    public static function fromArray(array $data, array $statusLogs = []): self
    {
        return new self(
            id: $data['id'],
            recipientId: $data['recipient_id'],
            channel: $data['channel'],
            priority: $data['priority'],
            message: $data['message'],
            status: $data['status'],
            externalId: $data['external_id'] ?? null,
            attempts: $data['attempts'],
            createdAt: $data['created_at'],
            updatedAt: $data['updated_at'],
            statusLogs: $statusLogs,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'recipient_id' => $this->recipientId,
            'channel' => $this->channel,
            'priority' => $this->priority,
            'message' => $this->message,
            'status' => $this->status,
            'external_id' => $this->externalId,
            'attempts' => $this->attempts,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
