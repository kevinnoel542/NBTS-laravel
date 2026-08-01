<?php

namespace App\Http\Requests\Api\V1;

use App\BloodGroup;
use App\Concerns\PasswordValidationRules;
use App\Gender;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MobileRegistrationRequest extends FormRequest
{
    use PasswordValidationRules;

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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'phone' => ['required', 'string', 'max:30', Rule::unique(User::class)],
            'password' => $this->passwordRules(),
            'blood_group' => ['required', Rule::enum(BloodGroup::class)],
            'gender' => ['required', Rule::enum(Gender::class)],
            'region' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', Rule::date()->beforeOrEqual(today())],
            'device_name' => ['required', 'string', 'max:100'],
        ];
    }

    /**
     * @return array{
     *     name: string,
     *     email: string|null,
     *     phone: string,
     *     password: string,
     *     blood_group: string,
     *     gender: string,
     *     region: string,
     *     date_of_birth: string
     * }
     */
    public function registrationData(): array
    {
        $data = $this->validated();

        return [
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'],
            'password' => $data['password'],
            'blood_group' => $data['blood_group'],
            'gender' => $data['gender'],
            'region' => $data['region'],
            'date_of_birth' => $data['date_of_birth'],
        ];
    }

    public function deviceName(): string
    {
        return $this->validated('device_name');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => $this->filled('email') ? mb_strtolower(trim((string) $this->input('email'))) : null,
            'phone' => trim((string) $this->input('phone')),
            'device_name' => $this->filled('device_name') ? $this->input('device_name') : 'NBTS Mobile',
        ]);
    }
}
