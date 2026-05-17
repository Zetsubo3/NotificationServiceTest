<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class MainRequest extends FormRequest
{
    /**
     * Обработка ошибок валидации.
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        $response = [
            'success' => false,
            'data' => [
                'errors' => $validator->errors()->toArray()
            ],
            'message' => 'The given data was invalid.',
            'error_code' => 'VALIDATION_ERROR',
            'http_status' => 422,
        ];

        throw new HttpResponseException(response()->json($response, $response['http_status']));
    }

    /**
     * Нормализация интовых параметров
     *
     * @param string $key
     * @param int $max
     * @param int|null $default
     * @return int|null
     */
    protected function normalizeIntParam(string $key, int $max, int|null $default): int|null
    {
        $value = $this->input($key);

        if ($value === '' || !is_numeric($value)) {
            return $default;
        }

        $intValue = (int) $value;

        if ($intValue < 1 || $intValue > $max) {
            return $default;
        }

        return $intValue;
    }

    /**
     * Нормализация enum параметров
     *
     * @param string $key
     * @param array $allowedValues
     * @param string|null $default
     * @return string|null
     */
    protected function normalizeEnumParam(string $key, array $allowedValues, ?string $default = null): ?string
    {
        $value = $this->input($key);

        if ($value === '' || $value === null) {
            return $default;
        }

        if (!in_array($value, $allowedValues, true)) {
            return null;
        }

        return $value;
    }

    /**
     * Нормализация параметра сортировки
     *
     * @param string $key
     * @return string|null
     */
    protected function normalizeSortParam(string $key): ?string
    {
        $value = $this->input($key);

        if ($value === null || $value === '') {
            return null;
        }

        if ($value === true || $value === 1 || $value === '1' || $value === 'true') {
            return 'asc';
        }
        if ($value === false || $value === 0 || $value === '0' || $value === 'false') {
            return 'desc';
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            if ($normalized === 'asc') {
                return 'asc';
            }
            if ($normalized === 'desc') {
                return 'desc';
            }
        }

        return null;
    }
}
