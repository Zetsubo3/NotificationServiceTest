<?php

namespace App\DTO;

class RecipientDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $email,
        public readonly string $phone,
        public readonly ?string $name,
        public readonly string $createdAt,
        public readonly string $updatedAt,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'phone' => $this->phone,
            'name' => $this->name,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
