<?php

namespace Database\Factories;

use App\Models\ExpertiseArea;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ExpertiseArea>
 */
class ExpertiseAreaFactory extends Factory
{
    public function definition(): array
    {
        $name = ucfirst(fake()->unique()->words(2, true));

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'active' => true,
            'sort_order' => 0,
        ];
    }
}
