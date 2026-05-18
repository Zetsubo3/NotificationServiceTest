<?php

namespace App\Exceptions;

use App\Exceptions\Recipients\DuplicateRecipientsException;
use App\Exceptions\Recipients\NoValidRecipientsException;
use App\Exceptions\Recipients\NoValidRecipientsWithDuplicatesException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Throwable;

class ApiExceptionHandler
{
    /**
     * Обрабатывает исключение и возвращает JSON ответ для API
     *
     * @param Throwable $e
     * @param Request $request
     * @return JsonResponse|null
     */
    public function render(Throwable $e, Request $request): ?JsonResponse
    {
        if (!$request->expectsJson()) {
            return null;
        }

        $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
        $errorCode = $this->getErrorCode($e);

        $response = [
            'success' => false,
            'data' => null,
            'message' => $e->getMessage(),
            'error_code' => $errorCode,
        ];

        if ($e instanceof NoValidRecipientsException) {
            $response['data'] = [
                'invalid_recipient_ids' => $e->getInvalidIds()
            ];
        }

        if ($e instanceof DuplicateRecipientsException) {
            $response['data'] = ['duplicate_recipient_ids' => $e->getDuplicateIds()];
        }

        if ($e instanceof ValidationException) {
            $response['message'] = 'The given data was invalid.';
            $response['errors'] = $e->errors();
        }

        if ($e instanceof NoValidRecipientsWithDuplicatesException) {
            $response['data'] = [
                'invalid_recipient_ids' => $e->getInvalidIds(),
                'duplicate_recipient_ids' => $e->getDuplicateIds(),
            ];
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Возвращает код ошибки в зависимости от типа исключения
     *
     * @param Throwable $e
     * @return string
     */
    private function getErrorCode(Throwable $e): string
    {
        return match (true) {
            $e instanceof NoValidRecipientsException => 'NO_VALID_RECIPIENTS',
            $e instanceof BadRequestHttpException => 'BAD_REQUEST',
            $e instanceof NoValidRecipientsWithDuplicatesException => 'NO_VALID_RECIPIENTS_WITH_DUPLICATES',
            $e instanceof AuthenticationException,
            $e instanceof UnauthorizedHttpException => 'UNAUTHORIZED',
            $e instanceof AccessDeniedHttpException => 'FORBIDDEN',
            $e instanceof NotFoundHttpException => 'NOT_FOUND',
            $e instanceof MethodNotAllowedHttpException => 'METHOD_NOT_ALLOWED',
            $e instanceof ConflictHttpException => 'CONFLICT',
            $e instanceof UnprocessableEntityHttpException,
            $e instanceof ValidationException => 'VALIDATION_ERROR',
            $e instanceof TooManyRequestsHttpException => 'TOO_MANY_REQUESTS',
            $e instanceof DuplicateRecipientsException => 'DUPLICATE_RECIPIENTS',
            default => 'INTERNAL_SERVER_ERROR',
        };
    }
}
