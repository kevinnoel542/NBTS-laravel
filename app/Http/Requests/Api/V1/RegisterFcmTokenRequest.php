<?php

namespace App\Http\Requests\Api\V1;

use App\DevicePlatform;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class RegisterFcmTokenRequest extends FormRequest
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
            'token' => ['required', 'string', 'between:20,255'],
            'device_type' => ['nullable', Rule::enum(DevicePlatform::class)],
        ];
    }

    public function fcmToken(): string
    {
        return trim((string) $this->validated('token'));
    }

    public function devicePlatform(): DevicePlatform
    {
        $deviceType = $this->validated('device_type');

        return is_string($deviceType)
            ? DevicePlatform::from($deviceType)
            : DevicePlatform::Android;
    }
}
