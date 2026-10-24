<?php

namespace Tests\Feature\Expertise;

use App\Enums\CollaboratorStatus;
use App\Models\Collaborator;
use App\Models\Credential;
use App\Models\Discipline;
use App\Models\ExpertiseArea;
use App\Models\Role;
use App\Models\User;
use App\Services\Expertise\CredentialLibrary;
use App\Services\Expertise\ExpertiseSearch;
use App\Services\Expertise\VocabularyReview;
use Database\Seeders\CredentialSeeder;
use Database\Seeders\ExpertiseVocabularySeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * The v1.0 Registers of September 2026, loaded as the Institute wrote them,
 * including the rules the documents state in prose.
 */
class RegistersV1Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ExpertiseVocabularySeeder::class);
    }

    private function collaborator(): User
    {
        $this->seed(RolePermissionSeeder::class);
        Role::query()->firstOrCreate(['name' => 'collaborator'], ['requires_mfa' => false]);

        $user = User::factory()->create();
        Collaborator::factory()->create(['user_id' => $user->id, 'status' => CollaboratorStatus::Active]);

        return $user;
    }

    public function test_the_distinguishing_lines_are_loaded_where_the_register_has_them(): void
    {
        $this->assertSame(29, Discipline::query()->whereNotNull('descriptor')->count());
        $this->assertStringStartsWith('For work focused on the health of populations', (string) Discipline::query()->where('name', 'Public Health')->value('descriptor'));
        $this->assertNull(Discipline::query()->where('name', 'Medicine')->value('descriptor'));
    }

    /** A line the Institute has already written is never overwritten by a release. */
    public function test_a_line_the_institute_edited_survives_a_release(): void
    {
        Discipline::query()->where('name', 'Public Health')->update(['descriptor' => 'The Institute prefers this wording.']);

        $this->seed(ExpertiseVocabularySeeder::class);

        $this->assertSame('The Institute prefers this wording.', Discipline::query()->where('name', 'Public Health')->value('descriptor'));
    }

    public function test_the_pensions_insurance_and_housing_specialties_arrived_and_the_old_one_retired(): void
    {
        // The live databases hold this as a Specialty from the v0.1 Register.
        ExpertiseArea::query()->firstOrCreate(
            ['slug' => 'pensions-retirement-benefits'],
            ['name' => 'Pensions & Retirement Benefits', 'search_key' => 'pensions and retirement benefits', 'active' => true, 'sort_order' => 999],
        );
        $this->seed(ExpertiseVocabularySeeder::class);

        foreach (['Occupational Pensions', 'Long-Term Care Insurance', 'Senior Housing', 'Psychometrics'] as $name) {
            $this->assertTrue(ExpertiseArea::query()->where('name', $name)->where('active', true)->exists(), $name);
        }

        // "Pensions & Retirement Benefits" is a Field in v1.0, not a Specialty.
        // The Specialty of that name stops being offered and is never deleted.
        $this->assertDatabaseHas('expertise_areas', ['name' => 'Pensions & Retirement Benefits', 'active' => false]);
        $this->assertDatabaseHas('disciplines', ['name' => 'Pensions & Retirement Benefits', 'active' => true]);

        $this->assertCount(0, app(ExpertiseSearch::class)->searchSpecialties('Pensions & Retirement Benefits'));
    }

    public function test_the_abbreviations_the_register_lists_lead_to_their_terms(): void
    {
        $match = app(ExpertiseSearch::class)->search('SLT')->first();

        $this->assertNotNull($match);
        $this->assertSame('Speech-Language Pathology', $match->name);
        $this->assertSame('also known as SLT', $match->explanation());
    }

    /**
     * Section 9 of the Alias Register: related but distinguishable terms are
     * not forced into one another. Kinesiology is not Exercise Science.
     */
    public function test_a_term_the_register_says_not_to_force_is_not_an_alias(): void
    {
        $names = app(ExpertiseSearch::class)->search('Kinesiology')->map(fn ($m) => $m->name)->all();

        $this->assertNotContains('Exercise Science', $names);
    }

    /**
     * Longevity is what the Institute is about, not a Field. Typing it must
     * explain rather than list everything longevity-related.
     */
    public function test_longevity_is_explained_rather_than_listed(): void
    {
        $response = $this->actingAs($this->collaborator())
            ->getJson(route('expertise.search', ['q' => 'Longevity', 'only' => 'field']))
            ->assertOk();

        $this->assertSame([], $response->json('results'));
        $this->assertStringContainsString('rather than a Field of its own', (string) $response->json('notice'));
    }

    /**
     * A credential may now carry who issues it and a plain line on what it
     * represents, so an unfamiliar credential can be explained later without a
     * rebuild. Both are optional and arrive at approval or from the library.
     */
    public function test_an_approved_credential_can_carry_its_issuer_and_a_plain_explanation(): void
    {
        Mail::fake();
        $this->seed(CredentialSeeder::class);
        $user = $this->collaborator();
        $profile = $user->collaborator->profile()->firstOrCreate([]);
        $founder = User::factory()->create();

        $proposal = app(CredentialLibrary::class)->propose($profile, $user, 'FFA', 'Fellow of the Faculty of Actuaries', 'United Kingdom');

        app(VocabularyReview::class)->approve(
            $proposal,
            $founder,
            null,
            null,
            [],
            'Institute and Faculty of Actuaries',
            'A senior actuarial qualification, historically awarded in Scotland.',
        );

        $credential = Credential::query()->where('acronym', 'FFA')->first();
        $this->assertNotNull($credential);
        $this->assertSame('Institute and Faculty of Actuaries', $credential->issuing_body);
        $this->assertStringStartsWith('A senior actuarial qualification', (string) $credential->description);
        $this->assertSame('Fellow of the Faculty of Actuaries (United Kingdom)', $credential->describe());
    }
}
