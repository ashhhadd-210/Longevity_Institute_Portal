<?php

namespace Database\Factories;

use App\Enums\InvitationStatus;
use App\Models\AdmissionDecision;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invitation>
 */
class InvitationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'token_hash' => Invitation::generateToken()['hash'],
            'expires_at' => now()->addDays(14),
            'status' => InvitationStatus::Issued->value,
            'admission_decision_id' => AdmissionDecision::factory(),
            'issued_by' => User::factory(),
            'send_count' => 0,
        ];
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvitationStatus::Accepted->value,
            'accepted_at' => now(),
        ]);
    }
}
