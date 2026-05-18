<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class LoginUserCommand extends Command
{
    protected $signature = 'user:login';
    protected $description = 'Авторизация пользователя и получение API токена';

    public function handle(): int
    {
        $this->info('Авторизация пользователя');
        $this->newLine();

        // Ввод email
        $email = $this->ask('Введите email');

        // Проверяем существование пользователя
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Пользователь с email '{$email}' не найден");
            return self::FAILURE;
        }

        // Ввод пароля
        $password = $this->secret('Введите пароль');

        // Проверка пароля
        if (!Hash::check($password, $user->password)) {
            $this->error('Неверный пароль');
            return self::FAILURE;
        }

        // Генерация нового токена
        $fullToken = $user->generateNewToken();

        // Обрезаем префикс (ID|)
        $token = explode('|', $fullToken, 2)[1] ?? $fullToken;

        $this->newLine();
        $this->info('✓ Авторизация успешна');
        $this->newLine();
        $this->info('Ваш API токен (Bearer):');
        $this->line($token);
        $this->newLine();
        $this->warn('Запишите токен - он больше не отобразится');

        return self::SUCCESS;
    }
}
