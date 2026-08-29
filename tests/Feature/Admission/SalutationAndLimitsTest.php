<?php

namespace Tests\Feature\Admission;

use App\Models\Application;
use App\Models\Invitation;
use App\Models\User;
use App\Support\FieldLimits;
use App\Support\Salutation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * How the Portal addresses people (agreed 28 August 2026) and the character
 * limits committed to on 25 August 2026.
 */
class SalutationAndLimitsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_preferred_name_is_used_in_place_of_the_first_name(): void
    {
        // The middle name is pinned rather than left to the factory, which
        // supplies one at random and would make the name of record vary.
        $user = User::factory()->create([
            'first_name' => 'Robert',
            'preferred_name' => 'Bob',
            'middle_name' => null,
            'last_name' => 'Smith',
        ]);

        $this->assertSame('Bob', $user->salutationName());
        $this->assertSame('Hello Bob', Salutation::greeting($user));

        // Address is not identity: the name of record is untouched.
        $this->assertSame('Robert Smith', $user->fullName());
    }

    public function test_the_first_name_is_used_when_no_preferred_name_was_given(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Robert',
            'preferred_name' => null,
            'last_name' => 'Smith',
        ]);

        $this->assertSame('Hello Robert', Salutation::greeting($user));
    }

    /**
     * The step that matters in practice. Before registration the Portal may
     * hold only what was typed, and in rare cases a single name. It must never
     * send "Hello ,".
     */
    public function test_the_portal_never_greets_with_an_empty_name(): void
    {
        $this->assertSame('Hello', Salutation::greeting(null));
        $this->assertSame('Hello', Salutation::greeting(''));
        $this->assertSame('Hello', Salutation::greeting('   '));
        $this->assertStringNotContainsString(' ,', Salutation::greeting(null).',');

        $invitation = Invitation::factory()->make(['first_name' => '', 'preferred_name' => null]);
        $this->assertSame('Dear', Salutation::greeting($invitation, 'Dear'));
    }

    /**
     * The preferred-name field absorbs the prefix question the Institute chose
     * not to model. Someone who wishes to be addressed as Doctor Smith simply
     * writes that.
     */
    public function test_the_preferred_name_can_carry_a_title_the_portal_does_not_model(): void
    {
        $user = User::factory()->create(['preferred_name' => 'Doctor Smith']);

        $this->assertSame('Dear Doctor Smith', Salutation::greeting($user, 'Dear'));
    }

    public function test_an_invitation_and_the_account_it_becomes_greet_identically(): void
    {
        $invitation = Invitation::factory()->create(['first_name' => 'Ada', 'preferred_name' => 'Addie']);
        $user = User::factory()->create(['first_name' => 'Ada', 'preferred_name' => 'Addie']);

        $this->assertSame(Salutation::greeting($invitation), Salutation::greeting($user));
    }

    public function test_the_agreed_limits_are_what_the_database_actually_holds(): void
    {
        $expected = [
            ['users', 'first_name', FieldLimits::NAME],
            ['users', 'preferred_name', FieldLimits::NAME],
            ['applications', 'organisation_text', FieldLimits::ORGANISATION],
            ['applications', 'professional_title', FieldLimits::TITLE],
            ['referrals', 'candidate_title', FieldLimits::TITLE],
            ['organisations', 'name', FieldLimits::ORGANISATION],
        ];

        foreach ($expected as [$table, $column, $limit]) {
            $actual = DB::selectOne(
                'select character_maximum_length as length from information_schema.columns where table_name = ? and column_name = ?',
                [$table, $column],
            );

            $this->assertSame($limit, (int) $actual->length, "{$table}.{$column} should hold {$limit} characters.");
        }
    }

    /**
     * Her professor: a named chair with a donor, a department and a joint
     * appointment. It must survive being stored and come back whole.
     */
    public function test_a_long_real_professional_title_is_stored_in_full(): void
    {
        $title = 'The Eleanor and Howard Rutherford Distinguished Professor of Health Economics '
            .'and Public Policy, Department of Health Services Research, and Senior Advisor to '
            .'the Institute for Population Ageing';

        $this->assertLessThanOrEqual(FieldLimits::TITLE, mb_strlen($title));

        $application = Application::factory()->create(['professional_title' => $title]);

        $this->assertSame($title, $application->fresh()->professional_title);
    }

    public function test_limits_are_counted_in_characters_not_bytes(): void
    {
        // Every character here is multi-byte, so a byte-counted limit of 100
        // would reject a name well within the agreed allowance.
        $name = str_repeat('Ž', 100);

        $this->assertSame(100, mb_strlen($name));
        $this->assertGreaterThan(100, strlen($name));

        $user = User::factory()->create(['last_name' => $name]);

        $this->assertSame($name, $user->fresh()->last_name);
    }
}
