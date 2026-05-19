<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Recipient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class NotificationFlowTest extends TestCase
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

        // Отключаем выполнение Job (проверяем только API)
        Queue::fake();
    }

    /**
     * Тест 1: запрос без токена возвращает 401
     */
    public function test_returns_401_without_token(): void
    {
        $payload = [
            'channel' => 'email',
            'message' => 'Test message',
            'recipient_ids' => [$this->recipient->id],
            'priority' => 'high',
        ];

        $response = $this->postJson('/api/send', $payload);

        $response->assertStatus(401);
        $response->assertJsonFragment([
            'error_code' => 'UNAUTHENTICATED'
        ]);
    }

    /**
     * Тест 1b: запрос с неверным токеном возвращает 401
     */
    public function test_returns_401_with_invalid_token(): void
    {
        $payload = [
            'channel' => 'email',
            'message' => 'Test message',
            'recipient_ids' => [$this->recipient->id],
            'priority' => 'high',
        ];

        $response = $this->postJson('/api/send', $payload, [
            'Authorization' => 'Bearer invalid-token-123'
        ]);

        $response->assertStatus(401);
    }

    /**
     * Тест 2: уведомление создаётся со статусом queued и записью в логе
     */
    public function test_creates_notification_with_queued_status_and_log(): void
    {
        $payload = [
            'channel' => 'email',
            'message' => 'Test message',
            'recipient_ids' => [$this->recipient->id],
            'priority' => 'high',
        ];

        $response = $this->postJson('/api/send', $payload, [
            'Authorization' => "Bearer {$this->token}"
        ]);

        $response->assertStatus(202);

        // Проверяем, что запись создалась со статусом queued
        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $this->recipient->id,
            'channel' => 'email',
            'priority' => 'high',
            'message' => 'Test message',
            'status' => 'queued',
            'attempts' => 0,
            'external_id' => null,
        ]);

        // Проверяем, что есть запись в логе статусов
        $notification = Notification::where('recipient_id', $this->recipient->id)->first();

        $this->assertDatabaseHas('notification_status_logs', [
            'notification_id' => $notification->id,
            'status' => 'queued',
        ]);
    }

    /**
     * Тест 3: валидация полей
     */
    public function test_validation_errors(): void
    {
        // Невалидный channel
        $payload = [
            'channel' => 'telegram',
            'message' => 'Test',
            'recipient_ids' => [$this->recipient->id],
            'priority' => 'high',
        ];

        $response = $this->postJson('/api/send', $payload, [
            'Authorization' => "Bearer {$this->token}"
        ]);

        $response->assertStatus(422);
        $this->assertEquals('VALIDATION_ERROR', $response->json('error_code'));
        $this->assertArrayHasKey('channel', $response->json('data.errors'));

        // Невалидный priority
        $payload = [
            'channel' => 'email',
            'message' => 'Test',
            'recipient_ids' => [$this->recipient->id],
            'priority' => 'invalid',
        ];

        $response = $this->postJson('/api/send', $payload, [
            'Authorization' => "Bearer {$this->token}"
        ]);

        $response->assertStatus(422);
        $this->assertArrayHasKey('priority', $response->json('data.errors'));

        // Пустой recipient_ids
        $payload = [
            'channel' => 'email',
            'message' => 'Test',
            'recipient_ids' => [],
            'priority' => 'high',
        ];

        $response = $this->postJson('/api/send', $payload, [
            'Authorization' => "Bearer {$this->token}"
        ]);

        $response->assertStatus(422);
        $this->assertArrayHasKey('recipient_ids', $response->json('data.errors'));
    }

    /**
     * Тест 4: дедупликация - повторное сообщение блокируется
     */
    public function test_duplicate_notification_is_blocked(): void
    {
        $payload = [
            'channel' => 'email',
            'message' => 'Duplicate test message',
            'recipient_ids' => [$this->recipient->id],
            'priority' => 'high',
        ];

        // Первый запрос - успех
        $response1 = $this->postJson('/api/send', $payload, [
            'Authorization' => "Bearer {$this->token}"
        ]);
        $response1->assertStatus(202);

        // Второй запрос - дубликат
        $response2 = $this->postJson('/api/send', $payload, [
            'Authorization' => "Bearer {$this->token}"
        ]);

        $response2->assertStatus(409);
        $response2->assertJsonFragment([
            'error_code' => 'DUPLICATE_RECIPIENTS'
        ]);
    }

    /**
     * Тест 5: уведомления не создаются для невалидных получателей
     */
    public function test_no_notifications_for_invalid_recipients(): void
    {
        $payload = [
            'channel' => 'email',
            'message' => 'Test message',
            'recipient_ids' => [9999900, 9999800], // несуществующие ID
            'priority' => 'high',
        ];

        $response = $this->postJson('/api/send', $payload, [
            'Authorization' => "Bearer {$this->token}"
        ]);

        $response->assertStatus(404);
        $response->assertJsonFragment([
            'error_code' => 'NO_VALID_RECIPIENTS'
        ]);

        // Проверяем, что уведомления не создались
        $this->assertDatabaseCount('notifications', 0);
        $this->assertDatabaseCount('notification_status_logs', 0);
    }

    /**
     * Тест 5b: частично валидные получатели
     */
    public function test_partially_valid_recipients(): void
    {
        $payload = [
            'channel' => 'email',
            'message' => 'Test message',
            'recipient_ids' => [$this->recipient->id, 99999],
            'priority' => 'high',
        ];

        $response = $this->postJson('/api/send', $payload, [
            'Authorization' => "Bearer {$this->token}"
        ]);

        $response->assertStatus(202);

        $response->assertJson([
            'success' => true,
            'data' => [
                'total_requested' => 2,
                'total_invalid' => 1,
                'total_queued' => 1,
            ]
        ]);

        // Проверяем, что создалось только одно уведомление
        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $this->recipient->id,
            'status' => 'queued',
        ]);
    }

    /**
     * Тест 6: история уведомлений с пагинацией
     */
    public function test_notification_history_pagination(): void
    {
        // Создаём 5 уведомлений вручную
        for ($i = 1; $i <= 5; $i++) {
            Notification::create([
                'recipient_id' => $this->recipient->id,
                'channel' => 'email',
                'priority' => 'low',
                'message' => "Test message {$i}",
                'status' => 'sent',
                'attempts' => 1,
                'external_id' => "ext_{$i}",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Первая страница, 2 записи
        $response = $this->getJson("/api/history/{$this->recipient->id}?page=1&count=2", [
            'Authorization' => "Bearer {$this->token}"
        ]);

        $response->assertStatus(200);
        $pagination = $response->json('data.pagination');

        $this->assertEquals(1, $pagination['current_page']);
        $this->assertEquals(2, $pagination['per_page']);
        $this->assertEquals(5, $pagination['total']);
        $this->assertEquals(3, $pagination['last_page']);
    }

    /**
     * Тест 8: получение истории для несуществующего получателя
     */
    public function test_history_for_nonexistent_recipient(): void
    {
        $response = $this->getJson('/api/history/9999999', [
            'Authorization' => "Bearer {$this->token}"
        ]);

        $response->assertStatus(404);
        $response->assertJsonFragment([
            'error_code' => 'NOT_FOUND',
        ]);
    }

    /**
     * Тест 10: массовая отправка с несколькими получателями
     */
    public function test_bulk_send_to_multiple_recipients(): void
    {
        // Создаём дополнительных получателей
        $recipient2 = Recipient::factory()->create([
            'email' => 'test2@example.com',
            'phone' => '+1987654321',
            'name' => 'Test User 2',
        ]);

        $recipient3 = Recipient::factory()->create([
            'email' => 'test3@example.com',
            'phone' => '+1122334455',
            'name' => 'Test User 3',
        ]);

        $payload = [
            'channel' => 'email',
            'message' => 'Bulk message',
            'recipient_ids' => [$this->recipient->id, $recipient2->id, $recipient3->id],
            'priority' => 'low',
        ];

        $response = $this->postJson('/api/send', $payload, [
            'Authorization' => "Bearer {$this->token}"
        ]);

        $response->assertStatus(202);
        $response->assertJson([
            'data' => [
                'total_queued' => 3,
                'total_requested' => 3,
            ]
        ]);

        $this->assertDatabaseCount('notifications', 3);
    }
}
