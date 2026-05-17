<?php

namespace App\Contracts\Repositories;

use App\DTO\RecipientDTO;

interface RecipientRepositoryInterface
{
    /**
     * Найти существующие ID получателей
     *
     * @param array $ids
     * @return array
     */
    public function findExistingIds(array $ids): array;

    /**
     * Найти получателя по ID
     *
     * @param int $id
     * @return RecipientDTO|null
     */
    public function findById(int $id): ?RecipientDTO;
}
