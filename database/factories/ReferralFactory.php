<?php

namespace Database\Factories;

use App\Enums\ReferralStatus;
use App\Models\Collaborator;
use App\Models\Referral;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Referral>
 */
class ReferralFactory extends Factory
{
    public function definition(): array
    {
        return [
            'referrer_collaborator_id' => Collaborator::factory(),
            'candidate_email' => fake()->unique()->safeEmail(),
            'candidate_first_name' => fake()->firstName(),
            'candidate_last_name' => fake()->lastName(),
            'candidate_organisation' => fake()->company(),
            'candidate_title' => fake()->jobTitle(),
            'candidate_country_code' => fake()->countryCode(),
            'candidate_expertise' => [fake()->word()],
            'reason' => fake()->paragraph(),
            'relationship_to_referrer' => 'Colleague',
            'comments' => null,
            'status' => ReferralStatus::Submitted->value,
        ];
    }
}
