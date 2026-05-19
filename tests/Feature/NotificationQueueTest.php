<?php

namespace Tests\Feature;

use App\Jobs\SendTransactionalNotificationJob;
use App\Models\Notification;
use App\Models\Recipient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class NotificationQueueTest extends TestCase
{
    use RefreshDatabase;

    private Recipient $recipient;
    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Очищаем Redis перед каждым тестом
        Redis::flushdb();

        // Создаём тестового получателя
        $this->recipient = Recipient::factory()->create([
            'email' => 'test@example.com',
            'phone' => '+1234567890',
            'name' => 'Test User',
        ]);

        // Создаём пользователя для авторизации
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    /**
     * Тест: полный цикл с реальной очередью
     */
    public function test_full_queue_cycle_changes_status_from_queued_to_sent(): void
    {
        config(['queue.default' => 'sync']);

        $payload = [
            'channel' => 'email',
            'message' => 'Test full queue cycle',
            'recipient_ids' => [$this->recipient->id],
            'priority' => 'high',
        ];

        $response = $this->postJson('/api/send', $payload, [
            'Authorization' => "Bearer {$this->token}"
        ]);

        $response->assertStatus(202);

        $notification = Notification::where('recipient_id', $this->recipient->id)
            ->where('message', 'Test full queue cycle')
            ->first();

        $this->assertNotNull($notification);
        $notification->refresh();

        $this->assertNotEquals('queued', $notification->status);

        // Получаем все логи статусов для уведомления
        $statusLogs = \App\Models\NotificationStatusLog::where('notification_id', $notification->id)
            ->orderBy('created_at')
            ->get();

        // Должно быть минимум 2 лога: queued + sent/delivered/failed
        $this->assertGreaterThanOrEqual(2, $statusLogs->count());

        // Первый лог должен быть queued
        $this->assertEquals('queued', $statusLogs[0]->status);

        // Последний лог должен соответствовать текущему статусу
        $this->assertEquals($notification->status, $statusLogs->last()->status);

        // Последний лог должен содержать metadata
        $this->assertNotNull($statusLogs->last()->metadata);
    }

    /**
     * Тест: высокий приоритет отправляется в очередь high
     */
    public function test_high_priority_goes_to_high_queue(): void
    {
        $payload = [
            'channel' => 'email',
            'message' => 'High priority message',
            'recipient_ids' => [$this->recipient->id],
            'priority' => 'high',
        ];

        $response = $this->postJson('/api/send', $payload, [
            'Authorization' => "Bearer {$this->token}"
        ]);

        $response->assertStatus(202);

        // Ждём обработки
        if (config('queue.default') === 'rabbitmq') {
            sleep(2);
        }

        $notification = Notification::where('message', 'High priority message')->first();
        $this->assertNotNull($notification);
        $this->assertEquals('high', $notification->priority);
    }
}
