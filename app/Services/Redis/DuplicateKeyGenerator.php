<?php

namespace App\Services\Redis;

class DuplicateKeyGenerator extends AbstractKeyGenerator
{
    public function __construct()
    {
        parent::__construct(config('redis.prefixes.duplicate_checker', 'notification:duplicate'));
    }

    /**
     * Генерация ключа для проверки дубликата
     *
     * @param int $recipientId
     * @param string $channel
     * @param string $message
     * @param string $priority
     * @return string
     */
    public function generate(int $recipientId, string $channel, string $message, string $priority): string
    {
        $hash = md5("{$recipientId}:{$channel}:{$message}:{$priority}");
        return $this->makeKey($hash);
    }

    /**
     * Генерация шаблона для очистки всех дублей получателя
     *
     * @param int $recipientId
     * @return string
     */

    public function generateRecipientPattern(int $recipientId): string
    {
        return $this->makeKey(md5("{$recipientId}:*")) . '*';
    }
}
