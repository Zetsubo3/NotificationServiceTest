<?php

namespace App\Exceptions\Recipients;

use Symfony\Component\HttpKernel\Exception\HttpException;

class DuplicateRecipientsException extends HttpException
{
    private array $duplicateIds;

    public function __construct(array $duplicateIds)
    {
        parent::__construct(
            statusCode: 409,
            message: 'Duplicate notifications detected',
        );

        $this->duplicateIds = $duplicateIds;
    }

    public function getDuplicateIds(): array
    {
        return $this->duplicateIds;
    }
}
