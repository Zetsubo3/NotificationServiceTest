<?php

namespace App\Http\Requests\Notifications;

use App\DTO\SendNotificationDTO;
use App\Http\Requests\MainRequest;

class SendRequest extends MainRequest
{
    public function rules(): array
    {
        return [
            'channel' => 'required|string|in:sms,email',
            'message' => 'required|string|max:1000',
            'recipient_ids' => 'required|array|min:1',
            'recipient_ids.*' => 'required|integer|min:1|max:9999999999',
            'priority' => 'sometimes|string|in:high,low',
        ];
    }

    public function messages(): array
    {
        return [
            'channel.required' => 'The channel field is required.',
            'channel.in' => 'The channel must be either "sms" or "email".',

            'message.required' => 'The message field is required.',
            'message.max' => 'The message must not exceed 1000 characters.',

            'recipient_ids.required' => 'At least one recipient ID is required.',
            'recipient_ids.array' => 'The recipient IDs must be an array.',
            'recipient_ids.min' => 'At least one recipient ID is required.',
            'recipient_ids.max' => 'You cannot send more than 10000 recipient IDs in one request.',

            'recipient_ids.*.required' => 'Each recipient ID is required.',
            'recipient_ids.*.integer' => 'Each recipient ID must be an integer.',
            'recipient_ids.*.min' => 'Each recipient ID must be at least 1.',
            'recipient_ids.*.max' => 'Each recipient ID must not exceed 9999999999.',
            'recipient_ids.*.exists' => 'One or more recipient IDs do not exist in the system.',

            'priority.string' => 'The priority must be a string.',
            'priority.in' => 'The priority must be either "high" or "low".',
        ];
    }

    public function toDTO(): SendNotificationDTO
    {
        return new SendNotificationDTO(
            channel: $this->input('channel'),
            message: $this->input('message'),
            recipientIds: $this->input('recipient_ids'),
            priority: $this->input('priority', 'low')
        );
    }
}

