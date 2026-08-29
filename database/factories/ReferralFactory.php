<?php

namespace Database\Factories;

use App\Enums\ReferralStatus;
use App\Models\Collaborator;
use App\Models\Referral;
use App\Models\ReferralRelationship;
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
            'comments' => null,
            'status' => ReferralStatus::Submitted->value,
        ];
    }

    /**
     * With the referrer's stated relationship, which is Institute-only
     * information and never leaves the review screens.
     */
    public function withRelationship(string $key = 'colleague', ?string $note = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'referral_relationship_id' => ReferralRelationship::query()->firstOrCreate(
                ['key' => $key],
                ['label' => ucfirst(str_replace('_', ' ', $key)), 'sort_order' => 0],
            )->id,
            'acquaintance_note' => $note,
        ]);
    }
}
