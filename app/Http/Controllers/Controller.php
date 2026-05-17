<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    /**
     * Форматирует стандартный JSON ответ
     *
     * @param mixed $data
     * @param string $message
     * @param bool $success
     * @param string|null $errorCode
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function formatResponse(
        mixed $data = [],
        string $message = '',
        bool $success = true,
        ?string $errorCode = null,
        int $statusCode = 200
    ): JsonResponse {
        return response()->json([
            'success' => $success,
            'data' => $data,
            'message' => $message,
            'error_code' => $errorCode,
        ], $statusCode);
    }
}
