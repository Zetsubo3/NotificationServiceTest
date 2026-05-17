<?php

namespace App\Providers;

use App\Services\Redis\DuplicateKeyGenerator;
use Illuminate\Support\ServiceProvider;

class RedisServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DuplicateKeyGenerator::class);
    }

    public function boot(): void
    {
        //
    }
}
