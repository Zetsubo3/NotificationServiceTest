<?php

namespace App\DTO;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PaginationDTO
{
    public function __construct(
        public readonly int $currentPage,
        public readonly int $lastPage,
        public readonly int $perPage,
        public readonly int $total,
    ) {}

    public static function fromPaginator(LengthAwarePaginator $paginator): self
    {
        return new self(
            currentPage: $paginator->currentPage(),
            lastPage: $paginator->lastPage(),
            perPage: $paginator->perPage(),
            total: $paginator->total(),
        );
    }

    public function toArray(): array
    {
        return [
            'current_page' => $this->currentPage,
            'last_page' => $this->lastPage,
            'per_page' => $this->perPage,
            'total' => $this->total,
        ];
    }
}
