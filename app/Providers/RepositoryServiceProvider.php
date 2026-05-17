<?php

namespace App\Providers;

use App\Contracts\Repositories\NotificationRepositoryInterface;
use App\Contracts\Repositories\RecipientRepositoryInterface;
use App\Repositories\NotificationRepository;
use App\Repositories\RecipientRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            NotificationRepositoryInterface::class,
            NotificationRepository::class
        );

        $this->app->bind(
            RecipientRepositoryInterface::class,
            RecipientRepository::class
        );
    }
}
