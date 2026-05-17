<?php

namespace App\Contracts\Requests;

interface FilterableRequestInterface
{
    /**
     * Возвращает отформатированные параметры запроса
     *
     * @return array
     */
    public function getRequestParams(): array;
}
