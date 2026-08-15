<?php

namespace Tests\Feature\Domain;

use App\Enums\CollaboratorStatus;
use App\Enums\VisibilityOverride;
use App\Models\Collaborator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Admitted ≠ visible (doc 02 §4): visibility is computed, with an
 * administrative override in either direction, and inactive collaborators are
 * never listed regardless of flags.
 */
class DirectoryVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_visibility_flag_controls_listing_by_default(): void
    {
        $hidden = Collaborator::factory()->create(['directory_visible' => false]);
        $visible = Collaborator::factory()->create(['directory_visible' => true]);

        $listedIds = Collaborator::query()->directoryListed()->pluck('id');

        $this->assertTrue($listedIds->contains($visible->id));
        $this->assertFalse($listedIds->contains($hidden->id));
    }

    public function test_force_hidden_overrides_a_visible_flag(): void
    {
        $collaborator = Collaborator::factory()->create([
            'directory_visible' => true,
            'visibility_override' => VisibilityOverride::ForceHidden->value,
        ]);

        $this->assertFalse($collaborator->isDirectoryListed());
        $this->assertFalse(
            Collaborator::query()->directoryListed()->pluck('id')->contains($collaborator->id)
        );
    }

    public function test_force_visible_overrides_a_hidden_flag(): void
    {
        $collaborator = Collaborator::factory()->create([
            'directory_visible' => false,
            'visibility_override' => VisibilityOverride::ForceVisible->value,
        ]);

        $this->assertTrue($collaborator->isDirectoryListed());
        $this->assertTrue(
            Collaborator::query()->directoryListed()->pluck('id')->contains($collaborator->id)
        );
    }

    public function test_inactive_collaborators_are_never_listed(): void
    {
        $collaborator = Collaborator::factory()->create([
            'status' => CollaboratorStatus::Inactive->value,
            'directory_visible' => true,
            'visibility_override' => VisibilityOverride::ForceVisible->value,
        ]);

        $this->assertFalse($collaborator->isDirectoryListed());
        $this->assertFalse(
            Collaborator::query()->directoryListed()->pluck('id')->contains($collaborator->id)
        );
    }
}
