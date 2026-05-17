<?php

namespace App\Http\Requests\Notifications;

use App\Http\Requests\MainRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class SubscriberNotificationsRequest extends MainRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return  [
            'page' => 'nullable',
            'count' => 'nullable',
            'status' => 'nullable',
            'channel' => 'nullable',
            'priority' => 'nullable',
            'created_at_sort' => 'nullable',
        ];
    }

    /**
     * Возвращает сообщения об ошибках для валидации.
     *
     * @return array
     */
    public function messages(): array
    {
        return [

        ];
    }

    public function getRequestParams(): array
    {
        return [
            'page' => $this->normalizeIntParam('page', PHP_INT_MAX, 1),
            'count' => $this->normalizeIntParam('count', 100, 15),
            'status' => $this->normalizeEnumParam('status', ['queued', 'sent', 'delivered', 'failed']),
            'channel' => $this->normalizeEnumParam('channel', ['sms', 'email']),
            'priority' => $this->normalizeEnumParam('priority', ['high', 'low']),
            'created_at_sort' => $this->normalizeSortParam('created_at_sort'),
        ];
    }
}
