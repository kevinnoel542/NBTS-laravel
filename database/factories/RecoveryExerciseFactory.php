<?php

namespace Database\Factories;

use App\Models\RecoveryExercise;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecoveryExercise>
 */
class RecoveryExerciseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'operator_id' => User::factory()->staff(),
            'approver_id' => User::factory()->staff(),
            'exercise_reference' => fake()->unique()->bothify('REC-########'),
            'scenario' => 'database_restore',
            'rto_minutes' => 240,
            'rpo_minutes' => 60,
            'recovery_point_at' => now()->subHour(),
            'recovered_at' => now(),
            'validation_checks' => ['login' => true, 'traceability' => true, 'quarantine_controls' => true],
            'exceptions' => [],
            'reopening_approved_at' => now(),
            'capa_reference' => null,
            'status' => 'passed',
        ];
    }
}
