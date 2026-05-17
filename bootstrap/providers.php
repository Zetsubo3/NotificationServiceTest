<?php

use App\Providers\AppServiceProvider;
use App\Providers\RepositoryServiceProvider;
use App\Providers\ServiceServiceProvider;

return [
    AppServiceProvider::class,
    ServiceServiceProvider::class,
    RepositoryServiceProvider::class,
];
