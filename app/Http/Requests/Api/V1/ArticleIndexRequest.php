<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class ArticleIndexRequest extends FormRequest
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
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'featured' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'between:1,50'],
        ];
    }

    public function search(): ?string
    {
        $search = $this->validated('q');

        return is_string($search) && trim($search) !== '' ? trim($search) : null;
    }

    public function category(): ?string
    {
        $category = $this->validated('category');

        return is_string($category) && trim($category) !== '' ? trim($category) : null;
    }

    public function featured(): ?bool
    {
        return $this->validated('featured') === null ? null : $this->boolean('featured');
    }

    public function perPage(): int
    {
        $perPage = $this->validated('per_page');

        return is_int($perPage) ? $perPage : 20;
    }
}
