<?php

namespace Tests\Feature\Domain;

use App\Models\Profile;
use App\Models\ProfileLink;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Up to five external links per profile — a hard cap enforced by the schema,
 * not only by interface validation (client standard).
 */
class ProfileLinkCapTest extends TestCase
{
    use RefreshDatabase;

    public function test_five_links_are_accepted_and_a_sixth_is_refused(): void
    {
        $profile = Profile::factory()->create();

        foreach (range(1, ProfileLink::MAX_PER_PROFILE) as $position) {
            $profile->links()->create([
                'url' => "https://example.org/{$position}",
                'label' => "Link {$position}",
                'position' => $position,
            ]);
        }

        $this->assertSame(5, $profile->links()->count());

        $this->expectException(QueryException::class);

        $profile->links()->create([
            'url' => 'https://example.org/6',
            'label' => 'One too many',
            'position' => 6,
        ]);
    }
}
