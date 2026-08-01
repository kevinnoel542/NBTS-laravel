<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class FirebaseAuthenticationRequest extends FormRequest
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
            'firebase_id_token' => ['required', 'string', 'max:10000'],
            'device_name' => ['required', 'string', 'max:100'],
        ];
    }

    /** @return array{firebase_id_token: string, device_name: string} */
    public function credentials(): array
    {
        /** @var array{firebase_id_token: string, device_name: string} $credentials */
        $credentials = $this->validated();

        return $credentials;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'device_name' => $this->filled('device_name') ? $this->input('device_name') : 'NBTS Mobile',
        ]);
    }
}
