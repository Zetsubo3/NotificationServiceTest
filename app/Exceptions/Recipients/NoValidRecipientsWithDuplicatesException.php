<?php

namespace App\Exceptions\Recipients;

use Symfony\Component\HttpKernel\Exception\HttpException;

class NoValidRecipientsWithDuplicatesException extends HttpException
{
    private array $invalidIds;
    private array $duplicateIds;

    public function __construct(array $invalidIds, array $duplicateIds)
    {
        parent::__construct(
            statusCode: 409,
            message: 'No valid recipients found',
        );

        $this->invalidIds = $invalidIds;
        $this->duplicateIds = $duplicateIds;
    }

    public function getInvalidIds(): array
    {
        return $this->invalidIds;
    }

    public function getDuplicateIds(): array
    {
        return $this->duplicateIds;
    }
}
