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
    ) {}

    public static function fromModel(Notification $notification): self
    {
        return new self(
            id: $notification->id,
            recipientId: $notification->recipient_id,
            channel: $notification->channel,
            priority: $notification->priority,
            message: $notification->message,
            status: $notification->status,
            externalId: $notification->external_id,
            attempts: $notification->attempts,
            createdAt: $notification->created_at->toISOString(),
            updatedAt: $notification->updated_at->toISOString(),
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
