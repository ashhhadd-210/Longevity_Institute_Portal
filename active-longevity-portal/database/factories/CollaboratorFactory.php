<?php

namespace Database\Factories;

use App\Enums\CollaboratorStatus;
use App\Models\Collaborator;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Collaborator>
 */
class CollaboratorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'invitation_id' => Invitation::factory()->accepted(),
            'status' => CollaboratorStatus::Active->value,
            'cohort' => 'wave_0',
            'directory_visible' => false,
            'visibility_override' => null,
            'admitted_at' => now(),
        ];
    }
}
