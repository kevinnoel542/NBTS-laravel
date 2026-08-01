<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppointmentSlotsRequest extends FormRequest
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
            'date' => [
                'required',
                Rule::date()
                    ->format('Y-m-d')
                    ->todayOrAfter()
                    ->beforeOrEqual(today()->addDays((int) config('nbts.appointment_booking_window_days', 90))),
            ],
        ];
    }

    public function appointmentDate(): string
    {
        return $this->validated('date');
    }
}
