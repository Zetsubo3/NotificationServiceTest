<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateUserCommand extends Command
{
    protected $signature = 'user:create';
    protected $description = 'Создание нового пользователя и генерация API токена';

    public function handle(): int
    {
        $this->info('Создание нового пользователя');
        $this->newLine();

        $email = $this->ask('Введите email пользователя');

        if (User::where('email', $email)->exists()) {
            $this->error("Пользователь с email '{$email}' уже существует");
            return self::FAILURE;
        }

        $password = $this->secret('Введите пароль');

        $user = User::create([
            'name' => explode('@', $email)[0],
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $fullToken = $user->generateNewToken();
        $token = explode('|', $fullToken, 2)[1] ?? $fullToken;

        $this->newLine();
        $this->info('✓ Пользователь успешно создан');
        $this->newLine();
        $this->info('Ваш API токен (Bearer):');
        $this->line($token);
        $this->newLine();
        $this->warn('Запишите токен - он больше не отобразится');
        $this->warn('Если токен утерян, вы можете заново авторизоваться по команде php artisan user:login');

        return self::SUCCESS;
    }
}
