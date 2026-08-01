<?php

namespace App\Http\Requests\Web;

use App\BloodGroup;
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
            'blood_group' => ['nullable', Rule::enum(BloodGroup::class)],
            'q' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', Rule::enum(CampaignType::class)],
        ];
    }

    public function search(): ?string
    {
        $search = $this->validated('q');

        return is_string($search) && trim($search) !== '' ? trim($search) : null;
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
}
