<?php

namespace Database\Factories;

use App\Enums\DecisionOutcome;
use App\Models\AdmissionDecision;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdmissionDecision>
 */
class AdmissionDecisionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'subject_type' => null,
            'subject_id' => null,
            'decision' => DecisionOutcome::Approved->value,
            'rationale' => fake()->sentence(),
            'decided_by' => User::factory(),
            'decided_at' => now(),
        ];
    }
}
