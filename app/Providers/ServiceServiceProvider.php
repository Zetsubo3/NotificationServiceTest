<?php

namespace App\Providers;

namespace App\Providers;

use App\Contracts\Services\DuplicateCheckerInterface;
use App\Contracts\Services\NotificationServiceInterface;
use App\Contracts\Services\QueueDispatcherServiceInterface;
use App\Services\DuplicateCheckerService;
use App\Services\NotificationService;
use App\Services\Queue\RabbitMQDispatcher;
use Illuminate\Support\ServiceProvider;

class ServiceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            NotificationServiceInterface::class,
            NotificationService::class
        );

        $this->app->bind(
            DuplicateCheckerInterface::class,
            DuplicateCheckerService::class
        );

        $this->app->bind(
            QueueDispatcherServiceInterface::class,
            RabbitMQDispatcher::class
        );

    }
}
