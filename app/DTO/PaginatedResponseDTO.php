<?php

namespace App\DTO;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PaginatedResponseDTO
{
    /**
     * @param array $items
     * @param PaginationDTO $pagination
     */
    public function __construct(
        public readonly array $items,
        public readonly PaginationDTO $pagination,
    ) {}

    public function toArray(): array
    {
        return [
            'data' => $this->items,
            'pagination' => $this->pagination->toArray(),
        ];
    }
}
