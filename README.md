# Notification Service
Сервис массовой отправки уведомлений (SMS/Email) с поддержкой приоритетов, дедубликации и асинхронной обработки через RabbitMQ.

## Технологии

- **PHP 8.2** + **Laravel 11**
- **PostgreSQL 15** - база данных
- **RabbitMQ 3.12** - брокер сообщений
- **Redis 7** - дедубликация и rate limiting
- **Docker** + **Docker Compose**

## Быстрый старт
#### Клонирование проекта
- git clone https://github.com/Zetsubo3/NotificationServiceTest
#### Настройка окружения
- cd NotificationServiceTest
- cp .env.docker .env
#### Разворот сервисов
- docker-compose up -d
#### База данных
##### Создать таблицы
- docker-compose exec app php artisan migrate
##### Запустить сидеры
- docker-compose exec app php artisan db:seed (создаст 100 получателей)
##### Создать пользователя для авторизации
- docker-compose exec app php artisan user:create (Возвращает токен)

## Использование
##### Авторизация
- docker-compose exec app php artisan user:login (возвращает токен)
##### API
- postman коллекция с документацией ендпоинтов NotificatonsCollectionV2.1.postman_collection.json лежит в корне проекта
