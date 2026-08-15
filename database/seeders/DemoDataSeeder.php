<?php

namespace Database\Seeders;

use App\Models\Collaborator;
use App\Models\ExpertiseArea;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Clearly synthetic community fixtures, including names that exercise the
 * Unicode extended-Latin requirement end to end. Local and testing only.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            ['first' => 'Søren', 'middle' => null, 'last' => 'Kierkegård'],
            ['first' => 'José', 'middle' => 'María', 'last' => 'Ñíguez'],
            ['first' => 'Zoë', 'middle' => null, 'last' => 'Åkerström'],
            ['first' => 'François', 'middle' => null, 'last' => 'Lefèvre'],
            ['first' => 'Bogdan', 'middle' => null, 'last' => 'Țîrle'],
        ];

        $expertise = ExpertiseArea::query()->pluck('id');

        foreach ($names as $i => $name) {
            $user = User::factory()->create([
                'first_name' => $name['first'],
                'middle_name' => $name['middle'],
                'last_name' => $name['last'],
                'email' => 'demo.collaborator.'.($i + 1).'@example.test',
            ]);

            $collaborator = Collaborator::factory()
                ->for($user)
                ->create(['directory_visible' => true]);

            Profile::factory()->for($collaborator)->create();

            $collaborator->expertiseAreas()->sync(
                $expertise->shuffle()->take(2)
            );
        }
    }
}
