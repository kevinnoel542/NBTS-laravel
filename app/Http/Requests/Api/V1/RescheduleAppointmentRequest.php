<?php

namespace App\Http\Requests\Api\V1;

use App\Models\BloodCenter;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RescheduleAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
        return [
            'blood_center_id' => ['sometimes', 'required', 'integer', Rule::exists(BloodCenter::class, 'id')],
            'scheduled_at' => ['required', Rule::date()->after(now())],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /** @return array{blood_center_id?: int, scheduled_at: string, notes: string|null} */
    public function appointmentData(): array
    {
        $data = $this->validated();
        $result = [
            'scheduled_at' => $data['scheduled_at'],
            'notes' => $data['notes'] ?? null,
        ];

        if (isset($data['blood_center_id'])) {
            $result['blood_center_id'] = (int) $data['blood_center_id'];
        }

        return $result;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('blood_center_id') && $this->has('center_id')) {
            $this->merge(['blood_center_id' => $this->input('center_id')]);
        }
    }
}
