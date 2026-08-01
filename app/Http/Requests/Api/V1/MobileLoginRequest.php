<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MobileLoginRequest extends FormRequest
{
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
            'identifier' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:100'],
        ];
    }

    /** @return array{identifier: string, password: string, device_name: string} */
    public function credentials(): array
    {
        /** @var array{identifier: string, password: string, device_name: string} $credentials */
        $credentials = $this->validated();

        return $credentials;
    }

    protected function prepareForValidation(): void
    {
        $identifier = $this->input('identifier', $this->input('email', $this->input('phone')));

        $this->merge([
            'identifier' => is_string($identifier) ? trim($identifier) : $identifier,
            'device_name' => $this->filled('device_name') ? $this->input('device_name') : 'NBTS Mobile',
        ]);
    }
}
