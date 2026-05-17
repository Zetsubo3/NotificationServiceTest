<?php

namespace App\Exceptions\Recipients;

use Symfony\Component\HttpKernel\Exception\HttpException;

class NoValidRecipientsException extends HttpException
{
    private array $invalidIds;

    public function __construct(array $invalidIds)
    {
        parent::__construct(
            statusCode: 404,
            message: 'No valid recipients found',
        );

        $this->invalidIds = $invalidIds;
    }

    public function getInvalidIds(): array
    {
        return $this->invalidIds;
    }
}
