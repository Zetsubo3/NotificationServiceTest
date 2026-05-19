<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string $methodName Название метода
     * @param int $maxAttempts Максимальное количество попыток за период
     * @param int $decaySeconds Время действия лимита в секундах
     * @param string ...$keyNames Ключи для проверки
     * @return Response
     */
    public function handle(
        Request $request,
        Closure $next,
        string $methodName,
        int $maxAttempts,
        int $decaySeconds,
        string ...$keyNames
    ): Response {
        // Формируем массив ключей из переданных названий

        if (app()->environment('testing')) {
            return $next($request);
        }

        $keys = [];
        foreach ($keyNames as $keyName) {
            $keys[] = $this->getValueFromRequest($request, $keyName);
        }

        foreach ($keys as $key) {
            $fullKey = $this->getFullKey($request, $methodName, $key);

            if (RateLimiter::tooManyAttempts($fullKey, $maxAttempts)) {
                $seconds = RateLimiter::availableIn($fullKey);

                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'There are too many requests. Try again later',
                    'error_code' => 'RATE_LIMIT_EXCEEDED',
                    'retry_after' => $seconds,
                ], 429);
            }
        }

        // Увеличиваем счётчики для каждого ключа
        foreach ($keys as $key) {
            $fullKey = $this->getFullKey($request, $methodName, $key);
            RateLimiter::hit($fullKey, $decaySeconds);
        }

        return $next($request);
    }

    /**
     * Получаем значение из запроса по имени ключа
     *
     * @param Request $request
     * @param string $keyName
     * @return string
     */
    private function getValueFromRequest(Request $request, string $keyName): string
    {
        return match(true) {
            // Специальные ключи
            $keyName === 'ip' => $request->ip(),
            $keyName === 'user_id' => $request->user()?->id ?? 'guest',
            $keyName === 'method' => $request->method(),
            $keyName === 'path' => $request->path(),
            $keyName === 'url' => $request->fullUrl(),

            // Параметры из body или query string
            $request->has($keyName) => $request->input($keyName),

            // Параметры из маршрута
            $request->route($keyName) !== null => (string) $request->route($keyName),

            // Заголовки запроса
            $request->header($keyName) !== null => $request->header($keyName),

            // Если ничего не найдено
            default => $keyName . '_not_found'
        };
    }

    /**
     * Формируем уникальный ключ для RateLimiter
     *
     * @param Request $request
     * @param string $methodName
     * @param string $key
     * @return string
     */
    private function getFullKey(Request $request, string $methodName, string $key): string
    {
        return implode(':', [
            'rate_limit',
            class_basename($request->route()->getControllerClass()),
            $methodName,
            $key
        ]);
    }
}
