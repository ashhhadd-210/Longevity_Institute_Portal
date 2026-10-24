<?php

namespace Database\Factories;

use App\Models\Collaborator;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Profile>
 */
class ProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'collaborator_id' => Collaborator::factory(),
            'professional_title' => fake()->jobTitle(),
            'one_line_summary' => Str::limit(fake()->sentence(10), 160, ''),
            'about' => fake()->paragraph(4),
            'photo_path' => null,
            'credentials' => null,
            'country_code' => fake()->countryCode(),
            'city' => fake()->city(),
            'state_region' => null,
            'languages' => ['English'],
            'collaboration_availability' => null,
            'organisation_id' => null,
            'completed_at' => null,
        ];
    }
}
