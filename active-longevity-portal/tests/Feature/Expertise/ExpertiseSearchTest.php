<?php

namespace Tests\Feature\Expertise;

use App\Models\Discipline;
use App\Services\Expertise\ExpertiseSearch;
use App\Support\ExpertiseMatch;
use Database\Seeders\ExpertiseVocabularySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The classification rules the Institute settled through September 2026, proven
 * against the real Registers rather than against invented fixtures.
 */
class ExpertiseSearchTest extends TestCase
{
    use RefreshDatabase;

    private ExpertiseSearch $search;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ExpertiseVocabularySeeder::class);
        $this->search = app(ExpertiseSearch::class);
    }

    public function test_the_registers_load_as_the_institute_wrote_them(): void
    {
        $this->assertDatabaseCount('disciplines', 67);
        $this->assertSame(434, \DB::table('discipline_expertise_area')->count());
    }

    /**
     * Punctuation the collaborator did not copy exactly must not cost them the
     * result.
     */
    public function test_punctuation_and_spelling_never_hide_a_term(): void
    {
        foreach ([
            'Genetics and Genomics' => 'Genetics & Genomics',
            'Genetics & Genomics' => 'Genetics & Genomics',
            'Speech Language Pathology' => 'Speech-Language Pathology',
            'Ageing Biology' => 'Aging Biology',
            'organisational behaviour' => 'Organizational Behavior',
        ] as $typed => $expected) {
            $names = $this->search->search($typed)->pluck('name')->all();
            $this->assertContains($expected, $names, "Typing '{$typed}' should find '{$expected}'.");
        }
    }

    /**
     * The point of the Alias Register: the collaborator's own word leads to the
     * Institute's word, and the Institute's word is what gets stored.
     */
    public function test_an_alias_surfaces_the_controlled_term_and_says_why(): void
    {
        $match = $this->search->search('Physiotherapy')->first();

        $this->assertNotNull($match);
        $this->assertSame('Physical Therapy', $match->name);
        $this->assertSame('also known as Physiotherapy', $match->explanation());
    }

    public function test_an_abbreviation_leads_to_the_full_term(): void
    {
        $names = $this->search->search('HR')->pluck('name')->all();

        $this->assertContains('Human Resources', $names);
    }

    /**
     * Identical or similar wording exists at both levels, so a result that does
     * not say which it is invites an accidental selection.
     */
    public function test_every_result_says_whether_it_is_a_field_or_a_specialty(): void
    {
        $results = $this->search->search('Architecture');

        $this->assertTrue($results->contains(fn (ExpertiseMatch $m): bool => $m->isField() && $m->name === 'Architecture & Urban Design'));
        $this->assertTrue($results->contains(fn (ExpertiseMatch $m): bool => ! $m->isField() && $m->name === 'Architecture'));

        foreach ($results as $result) {
            $this->assertContains($result->label(), ['Field', 'Specialty']);
        }
    }

    public function test_an_exact_name_outranks_everything_else(): void
    {
        $first = $this->search->search('Medicine')->first();

        $this->assertNotNull($first);
        $this->assertSame('Medicine', $first->name);
        $this->assertTrue($first->isField());
    }

    /**
     * The interdisciplinary point. A Specialty belongs to as many Fields as
     * genuinely apply, and searching any of them finds the person.
     */
    public function test_a_specialty_carries_every_field_it_genuinely_belongs_to(): void
    {
        $match = $this->search->search('Nutritional Epidemiology')
            ->first(fn (ExpertiseMatch $m): bool => ! $m->isField());

        $this->assertNotNull($match);
        $this->assertStringContainsString('Epidemiology', $match->context);
        $this->assertStringContainsString('Nutrition Science', $match->context);
    }

    public function test_public_health_nutrition_spans_its_three_fields(): void
    {
        $match = $this->search->search('Public Health Nutrition')
            ->first(fn (ExpertiseMatch $m): bool => ! $m->isField());

        $this->assertNotNull($match);

        foreach (['Dietetics', 'Nutrition Science', 'Public Health'] as $field) {
            $this->assertStringContainsString($field, $match->context);
        }
    }

    /**
     * Choosing a Field first shortens the list. It must never shorten the
     * choices, or the interdisciplinary collaborator meets the hardest path.
     */
    public function test_narrowing_by_field_reorders_but_never_excludes(): void
    {
        $unnarrowed = $this->search->searchSpecialties('Epidemiology', null, 100);
        $epidemiology = Discipline::query()->where('name', 'Epidemiology')->firstOrFail();
        $narrowed = $this->search->searchSpecialties('Epidemiology', $epidemiology->id, 100);

        $this->assertSame(
            $unnarrowed->pluck('name')->sort()->values()->all(),
            $narrowed->pluck('name')->sort()->values()->all(),
            'Narrowing to a Field must change the order, never the available choices.'
        );
    }

    public function test_searching_nonsense_returns_nothing_rather_than_guessing(): void
    {
        $this->assertCount(0, $this->search->search('qqzzxx'));
        $this->assertCount(0, $this->search->search('   '));
    }

    /**
     * The three Fields added after implementation review, because the Register
     * described the professions around each domain without giving the
     * practitioner inside it a home.
     */
    public function test_the_three_fields_added_after_review_are_present(): void
    {
        foreach (['Insurance', 'Housing', 'Pensions & Retirement Benefits'] as $field) {
            $this->assertTrue(
                $this->search->searchFields($field)->contains(fn (ExpertiseMatch $m): bool => $m->name === $field),
                "{$field} should be selectable as a Field."
            );
        }
    }
}
