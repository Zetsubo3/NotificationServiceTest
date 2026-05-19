FROM php:8.2-cli

# Обновление и установка базовых пакетов
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    && rm -rf /var/lib/apt/lists/*

# Установка librabbitmq-dev отдельно (может быть проблема в нём)
RUN apt-get update && apt-get install -y --no-install-recommends \
    librabbitmq-dev \
    && rm -rf /var/lib/apt/lists/*

# Установка PHP расширений
RUN docker-php-ext-install pdo_pgsql pdo_sqlite zip bcmath sockets

# Установка Redis расширения
RUN pecl install redis && docker-php-ext-enable redis

# Установка RabbitMQ C library
RUN pecl install amqp && docker-php-ext-enable amqp

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

RUN composer install --no-interaction --optimize-autoloader --no-dev

EXPOSE 8000
