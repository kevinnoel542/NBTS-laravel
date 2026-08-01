<?php

namespace App\Http\Requests\Api\V1;

use App\BloodGroup;
use App\Gender;
use App\Models\BloodCenter;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof User;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();
        $userId = $user instanceof User ? $user->id : null;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['sometimes', 'required', 'string', 'max:30', Rule::unique(User::class)->ignore($userId)],
            'blood_group' => ['sometimes', 'required', Rule::enum(BloodGroup::class)],
            'gender' => ['sometimes', 'required', Rule::enum(Gender::class)],
            'date_of_birth' => ['sometimes', 'required', Rule::date()->beforeOrEqual(today())],
            'region' => ['sometimes', 'required', 'string', 'max:255'],
            'address' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'preferred_center_id' => [
                'sometimes',
                'nullable',
                Rule::exists(BloodCenter::class, 'id')->where('is_active', true),
            ],
            'emergency_contact_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'push_notifications_enabled' => ['sometimes', 'boolean'],
            'sms_reminders_enabled' => ['sometimes', 'boolean'],
            'share_anonymized_data' => ['sometimes', 'boolean'],
            'language' => ['sometimes', 'required', Rule::in(['en', 'sw'])],
        ];
    }

    /** @return array<string, mixed> */
    public function profileData(): array
    {
        return $this->validated();
    }

    protected function prepareForValidation(): void
    {
        $prepared = [];

        if ($this->has('phone')) {
            $prepared['phone'] = trim((string) $this->input('phone'));
        }

        if ($this->has('language')) {
            $prepared['language'] = match ($this->input('language')) {
                'English' => 'en',
                'Swahili' => 'sw',
                default => $this->input('language'),
            };
        }

        $this->merge($prepared);
    }
}
