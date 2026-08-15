<?php

namespace Database\Factories;

use App\Models\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Organisation>
 */
class OrganisationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'name_original' => null,
            'website' => fake()->boolean(60) ? fake()->url() : null,
            'country_code' => fake()->countryCode(),
        ];
    }
}
