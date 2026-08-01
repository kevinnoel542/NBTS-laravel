<?php

namespace App\Http\Requests\Api\V1;

use App\BloodGroup;
use App\CampaignStatus;
use App\CampaignType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CampaignIndexRequest extends FormRequest
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
            'status' => [
                'nullable',
                Rule::enum(CampaignStatus::class)->only([
                    CampaignStatus::Upcoming,
                    CampaignStatus::Ongoing,
                ]),
            ],
            'type' => ['nullable', Rule::enum(CampaignType::class)],
            'blood_group' => ['nullable', Rule::enum(BloodGroup::class)],
            'center_id' => ['nullable', 'integer', 'min:1', 'exists:blood_centers,id'],
            'per_page' => ['nullable', 'integer', 'between:1,50'],
        ];
    }

    public function search(): ?string
    {
        $search = $this->validated('q');

        return is_string($search) && trim($search) !== '' ? trim($search) : null;
    }

    public function status(): ?CampaignStatus
    {
        $status = $this->validated('status');

        return is_string($status) ? CampaignStatus::tryFrom($status) : null;
    }

    public function campaignType(): ?CampaignType
    {
        $type = $this->validated('type');

        return is_string($type) ? CampaignType::tryFrom($type) : null;
    }

    public function bloodGroup(): ?BloodGroup
    {
        $bloodGroup = $this->validated('blood_group');

        return is_string($bloodGroup) ? BloodGroup::tryFrom($bloodGroup) : null;
    }

    public function centerId(): ?int
    {
        $centerId = $this->validated('center_id');

        return is_int($centerId) ? $centerId : null;
    }

    public function perPage(): int
    {
        $perPage = $this->validated('per_page');

        return is_int($perPage) ? $perPage : 20;
    }
}
