<?php

namespace Database\Seeders;

use App\Models\ScreeningProtocol;
use App\ScreeningProtocolStatus;
use Illuminate\Database\Seeder;

class ScreeningProtocolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ScreeningProtocol::query()->updateOrCreate(
            [
                'code' => config('phase-six.screening.construction_protocol_code'),
                'version' => config('phase-six.screening.construction_protocol_version'),
            ],
            [
                'title' => 'Whole blood donor screening — construction protocol',
                'status' => ScreeningProtocolStatus::Active,
                'questionnaire' => [
                    ['key' => 'consent_confirmed', 'label' => 'Screening consent confirmed', 'required' => true, 'type' => 'boolean'],
                    ['key' => 'feels_well', 'label' => 'Donor reports feeling well today', 'required' => true, 'type' => 'boolean'],
                    ['key' => 'self_exclusion', 'label' => 'Confidential self-exclusion', 'required' => true, 'type' => 'boolean'],
                ],
                'rules' => [
                    'minimum_age' => config('phase-six.screening.minimum_age'),
                    'maximum_age' => config('phase-six.screening.maximum_age'),
                    'minimum_weight_kg' => config('phase-six.screening.minimum_weight_kg'),
                    'disqualifying_answers' => ['feels_well' => false, 'self_exclusion' => true],
                ],
                'effective_from' => today(),
                'effective_until' => null,
                'is_construction_only' => true,
                'approved_by' => null,
                'approved_at' => null,
            ],
        );
    }
}
