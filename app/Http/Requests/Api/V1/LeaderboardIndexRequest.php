<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class LeaderboardIndexRequest extends FormRequest
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
            'period' => ['nullable', 'in:all_time'],
            'per_page' => ['nullable', 'integer', 'between:1,50'],
        ];
    }

    public function period(): string
    {
        $period = $this->validated('period');

        return is_string($period) ? $period : 'all_time';
    }

    public function perPage(): int
    {
        return $this->integer('per_page', 20);
    }
}
