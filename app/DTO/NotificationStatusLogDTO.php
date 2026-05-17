<?php

namespace App\DTO;

use App\Models\NotificationStatusLog;

class NotificationStatusLogDTO
{
    public function __construct(
        public readonly string $status,
        public readonly ?array $metadata,
        public readonly string $createdAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: $data['status'],
            metadata: $data['metadata'] ?? null,
            createdAt: $data['created_at'],
        );
    }

    public function toArray(): array
    {
        $result = [
            'status' => $this->status,
            'created_at' => $this->createdAt,
        ];

        if ($this->metadata) {
            $result['metadata'] = $this->metadata;
        }

        return $result;
    }
}
