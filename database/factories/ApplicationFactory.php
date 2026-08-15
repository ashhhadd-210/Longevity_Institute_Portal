<?php

namespace Database\Factories;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Application>
 */
class ApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'first_name' => fake()->firstName(),
            'middle_name' => null,
            'last_name' => fake()->lastName(),
            'organisation_text' => fake()->company(),
            'professional_title' => fake()->jobTitle(),
            'country_code' => fake()->countryCode(),
            'expertise_claimed' => [fake()->word()],
            'motivation' => fake()->paragraph(),
            'status' => ApplicationStatus::Submitted->value,
            'submitted_at' => now(),
        ];
    }
}
