<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class NotificationIndexRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'unread' => ['nullable', 'boolean'],
            'type' => ['nullable', 'string', 'max:50'],
            'per_page' => ['nullable', 'integer', 'between:1,50'],
        ];
    }

    public function unread(): ?bool
    {
        return $this->validated('unread') === null ? null : $this->boolean('unread');
    }

    public function notificationType(): ?string
    {
        $type = $this->validated('type');

        return is_string($type) && trim($type) !== '' ? trim($type) : null;
    }

    public function perPage(): int
    {
        $perPage = $this->validated('per_page');

        return is_int($perPage) ? $perPage : 20;
    }
}
