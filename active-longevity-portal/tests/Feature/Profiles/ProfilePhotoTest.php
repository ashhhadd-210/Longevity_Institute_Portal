<?php

namespace Tests\Feature\Profiles;

use App\Enums\CollaboratorStatus;
use App\Models\Collaborator;
use App\Models\Role;
use App\Models\User;
use App\Services\Profiles\ProfilePhotos;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The photograph rules a machine can enforce, enforced.
 *
 * Head and shoulders, no hats and recency are for the Institute's eye, not for
 * a test. What is checked here is the part that is ours: format, size, no
 * animation, and every photograph arriving at the same rectangle.
 */
class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed(RolePermissionSeeder::class);
        Role::query()->firstOrCreate(['name' => 'collaborator'], ['requires_mfa' => false]);
    }

    private function collaborator(): User
    {
        $user = User::factory()->create();
        Collaborator::factory()->create([
            'user_id' => $user->id,
            'status' => CollaboratorStatus::Active,
            'directory_visible' => true,
        ]);

        return $user;
    }

    /**
     * Whatever shape arrives, a portrait rectangle comes out. Landscape from a
     * phone held sideways, square from a social profile, all the same.
     */
    public function test_every_photograph_is_normalised_to_the_agreed_rectangle(): void
    {
        $user = $this->collaborator();

        foreach ([[1600, 900], [500, 500], [300, 900]] as [$w, $h]) {
            $this->actingAs($user)
                ->post(route('profile.photo'), ['photo' => UploadedFile::fake()->image('p.jpg', $w, $h)])
                ->assertRedirect(route('profile.edit'))
                ->assertSessionHas('status');

            $path = $user->fresh()?->collaborator?->profile?->photo_path;
            $this->assertNotNull($path);
            Storage::disk('public')->assertExists($path);

            [$outW, $outH] = getimagesize(Storage::disk('public')->path($path));
            $this->assertSame(ProfilePhotos::WIDTH, $outW, "A {$w}x{$h} upload should become ".ProfilePhotos::WIDTH.' wide.');
            $this->assertSame(ProfilePhotos::HEIGHT, $outH);
        }
    }

    public function test_replacing_a_photograph_removes_the_old_one(): void
    {
        $user = $this->collaborator();

        $this->actingAs($user)->post(route('profile.photo'), ['photo' => UploadedFile::fake()->image('a.jpg', 800, 800)]);
        $first = $user->fresh()?->collaborator?->profile?->photo_path;

        $this->actingAs($user)->post(route('profile.photo'), ['photo' => UploadedFile::fake()->image('b.jpg', 800, 800)]);
        $second = $user->fresh()?->collaborator?->profile?->photo_path;

        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing((string) $first);
        Storage::disk('public')->assertExists((string) $second);
    }

    public function test_a_gif_is_refused_because_it_might_move(): void
    {
        $user = $this->collaborator();

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->post(route('profile.photo'), ['photo' => UploadedFile::fake()->image('anim.gif', 400, 400)])
            ->assertSessionHasErrors('photo');

        $this->assertNull($user->fresh()?->collaborator?->profile?->photo_path);
    }

    public function test_a_document_pretending_to_be_a_photograph_is_refused(): void
    {
        $user = $this->collaborator();

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->post(route('profile.photo'), ['photo' => UploadedFile::fake()->create('cv.pdf', 200, 'application/pdf')])
            ->assertSessionHasErrors('photo');
    }

    public function test_a_photograph_can_be_removed(): void
    {
        $user = $this->collaborator();

        $this->actingAs($user)->post(route('profile.photo'), ['photo' => UploadedFile::fake()->image('a.jpg', 800, 800)]);
        $path = (string) $user->fresh()?->collaborator?->profile?->photo_path;

        $this->actingAs($user)->delete(route('profile.photo.destroy'))->assertRedirect();

        $this->assertNull($user->fresh()?->collaborator?->profile?->photo_path);
        Storage::disk('public')->assertMissing($path);
    }
}
