<?php

namespace Tests\Feature\Visibility;

use App\Enums\Audience;
use App\Support\Visibility\VisibilityRegister;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

/**
 * The visibility architecture the Institute approved on 29 August 2026, checked
 * field by field and viewer by viewer.
 *
 * The aim is not that the Institute trusts us to have assumed correctly. It is
 * that no assumption stays invisible long enough to matter.
 */
class VisibilityRegisterTest extends TestCase
{
    /**
     * The promise that binds every other rule: a collaborator may narrow what
     * is shown about them, and may never widen it.
     */
    public function test_a_collaborator_can_narrow_but_never_widen(): void
    {
        $about = VisibilityRegister::for('about');

        $this->assertSame(Audience::Community, $about->effective(null));
        $this->assertSame(Audience::Collaborator, $about->effective(Audience::Collaborator));
        $this->assertSame(Audience::Institute, $about->effective(Audience::Institute));

        // Reaching past the ceiling changes nothing.
        $this->assertSame(Audience::Community, $about->effective(Audience::Public));
    }

    /**
     * Where the Institute has not offered a choice, a collaborator's preference
     * cannot loosen the rule either.
     */
    public function test_a_choice_cannot_widen_a_field_that_offers_no_choice(): void
    {
        $city = VisibilityRegister::for('city');

        $this->assertFalse($city->collaboratorMayNarrow);
        $this->assertSame(Audience::Institute, $city->effective(Audience::Public));
        $this->assertSame(Audience::Institute, $city->effective(Audience::Community));
    }

    /**
     * The fourth rung exists and stands empty. If this test ever fails,
     * something has been published to the open web and somebody meant to.
     */
    public function test_nothing_at_all_is_visible_on_the_open_web(): void
    {
        foreach (VisibilityRegister::all() as $name => $field) {
            $this->assertNotSame(
                Audience::Public,
                $field->ceiling,
                "'{$name}' is set to be visible to anyone on the web. Nothing should be."
            );
        }
    }

    /**
     * City and state were collected as administrative and described as such at
     * the time. That promise binds us afterwards, and binds us even for a
     * collaborator who would happily waive it, because the assurance was given
     * to everybody collectively.
     */
    public function test_the_promise_made_at_collection_holds(): void
    {
        $this->assertSame(Audience::Institute, VisibilityRegister::for('city')->ceiling);
        $this->assertSame(Audience::Institute, VisibilityRegister::for('state_region')->ceiling);
        $this->assertSame(Audience::Community, VisibilityRegister::for('country_code')->ceiling);
    }

    /** An address that is never displayed cannot be harvested. */
    public function test_an_email_address_is_never_shown_to_the_community(): void
    {
        $this->assertFalse(VisibilityRegister::visible('email', Audience::Community));
        $this->assertTrue(VisibilityRegister::visible('email', Audience::Collaborator));
    }

    /**
     * A referrer's account of how they know somebody was given in confidence
     * about a person who did not know they were being discussed.
     */
    public function test_what_a_referrer_said_stays_with_the_institute_forever(): void
    {
        foreach (['referral_relationship', 'referral_note', 'application_motivation', 'review_decisions'] as $field) {
            $this->assertSame(
                Audience::Institute,
                VisibilityRegister::for($field)->ceiling,
                "'{$field}' was given in confidence and must never reach the community."
            );
            $this->assertFalse(VisibilityRegister::visible($field, Audience::Community));
        }
    }

    /** A field nobody declared is not published by accident. */
    public function test_an_undeclared_field_belongs_to_the_institute_alone(): void
    {
        $unknown = VisibilityRegister::for('something_nobody_thought_about');

        $this->assertSame(Audience::Institute, $unknown->ceiling);
        $this->assertFalse(VisibilityRegister::visible('something_nobody_thought_about', Audience::Community));
    }

    /** And the build refuses to pass until somebody states an intention. */
    public function test_the_suite_refuses_an_undeclared_field(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/no declared visibility/');

        VisibilityRegister::assertComplete(['first_name', 'a_brand_new_column']);
    }

    /**
     * The guard that matters in a year, when somebody adds a column and forgets
     * this file exists. Every stored profile field must be declared.
     */
    public function test_every_column_the_portal_stores_about_a_person_is_declared(): void
    {
        $stored = collect(DB::select("
            SELECT column_name FROM information_schema.columns
            WHERE table_name = 'profiles'
        "))->pluck('column_name')
            ->reject(fn (string $c): bool => in_array($c, [
                'id', 'collaborator_id', 'completed_at', 'created_at', 'updated_at', 'deleted_at',
            ], true))
            ->values()
            ->all();

        VisibilityRegister::assertComplete($stored);
        $this->assertNotEmpty($stored);
    }

    #[DataProvider('viewers')]
    public function test_each_viewer_sees_exactly_what_they_should(Audience $viewer, string $field, bool $expected): void
    {
        $this->assertSame($expected, VisibilityRegister::visible($field, $viewer));
    }

    /** @return array<string, array{Audience, string, bool}> */
    public static function viewers(): array
    {
        return [
            'the Institute sees the city' => [Audience::Institute, 'city', true],
            'the Institute sees everything' => [Audience::Institute, 'review_decisions', true],
            'a stranger sees nothing at all' => [Audience::Public, 'professional_title', false],
            'the community sees a title' => [Audience::Community, 'professional_title', true],
            'the community sees the country' => [Audience::Community, 'country_code', true],
            'the community never sees the city' => [Audience::Community, 'city', false],
            'the community never sees an email' => [Audience::Community, 'email', false],
            'a collaborator sees their own email' => [Audience::Collaborator, 'email', true],
            'a collaborator never sees a review decision' => [Audience::Collaborator, 'review_decisions', false],
        ];
    }
}
