<?php

namespace App\Services;

use App\Contracts\Services\DuplicateCheckerInterface;
use App\Services\Redis\DuplicateKeyGenerator;

class DuplicateCheckerService implements DuplicateCheckerInterface
{
    private mixed $redis;

    public function __construct(
        private readonly DuplicateKeyGenerator $keyGenerator
    ) {
        $this->redis = app('redis');
    }

    public function isDuplicate(int $recipientId, string $channel, string $message, string $priority): bool
    {
        $key = $this->keyGenerator->generate($recipientId, $channel, $message, $priority);
        return (bool) $this->redis->exists($key);
    }

    public function markAsSent(int $recipientId, string $channel, string $message, string $priority, int $ttl = 60): void
    {
        $key = $this->keyGenerator->generate($recipientId, $channel, $message, $priority);
        $this->redis->setex($key, $ttl, 'sent');
    }
}
