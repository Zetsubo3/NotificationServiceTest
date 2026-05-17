<?php

namespace App\Services\Redis;

abstract class AbstractKeyGenerator
{
    protected string $prefix;

    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    protected function makeKey(string $identifier): string
    {
        return $this->prefix . ':' . $identifier;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }
}
