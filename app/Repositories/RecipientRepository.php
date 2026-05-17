<?php

namespace App\Repositories;

use App\Contracts\Repositories\RecipientRepositoryInterface;
use App\DTO\RecipientDTO;
use App\Models\Recipient;

class RecipientRepository implements RecipientRepositoryInterface
{
    public function findExistingIds(array $ids): array
    {
        return Recipient::query()->whereIn('id', $ids)
            ->pluck('id')
            ->toArray();
    }

    public function findById(int $id): ?RecipientDTO
    {
        $recipient = Recipient::query()->find($id);

        if (!$recipient) {
            return null;
        }

        return $this->mapToDTO($recipient);
    }

    /**
     * Маппинг модели в DTO
     *
     * @param Recipient $recipient
     * @return RecipientDTO
     */
    private function mapToDTO(Recipient $recipient): RecipientDTO
    {
        return new RecipientDTO(
            id: $recipient->id,
            email: $recipient->email,
            phone: $recipient->phone,
            name: $recipient->name,
            createdAt: $recipient->created_at->toISOString(),
            updatedAt: $recipient->updated_at->toISOString(),
        );
    }
}
