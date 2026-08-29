<?php

namespace Tests\Feature\Admission;

use App\Enums\MatchConfidence;
use App\Models\Application;
use App\Models\CandidateCase;
use App\Models\Referral;
use App\Models\User;
use App\Services\Admission\CandidateCaseService;
use App\Services\Admission\ReviewService;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * One person, one case, one decision, one invitation, one admission history
 * (agreed 18 August 2026).
 *
 * The rule these tests exist to protect is that nothing merges by itself. A
 * wrongly joined pair of people is far harder to repair than an unjoined one.
 */
class CandidateCaseTest extends TestCase
{
    use RefreshDatabase;

    private function service(): CandidateCaseService
    {
        return app(CandidateCaseService::class);
    }

    public function test_both_submissions_are_kept_and_neither_is_discarded(): void
    {
        $application = Application::factory()->create(['email' => 'ada@example.org']);
        $referral = Referral::factory()->create(['candidate_email' => 'ada@example.org']);

        $this->service()->openFor($application);
        $this->service()->openFor($referral);

        $this->assertSame(2, CandidateCase::query()->count());
        $this->assertNotNull($application->fresh()->candidate_case_id);
        $this->assertNotNull($referral->fresh()->candidate_case_id);
    }

    public function test_a_shared_email_address_is_a_confident_match_but_still_only_a_suggestion(): void
    {
        $application = Application::factory()->create([
            'email' => 'ada@example.org',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
        ]);
        $referral = Referral::factory()->create([
            'candidate_email' => 'ada@example.org',
            'candidate_first_name' => 'Ada',
            'candidate_last_name' => 'Lovelace',
        ]);

        $caseA = $this->service()->openFor($application);
        $this->service()->openFor($referral);

        $matches = $this->service()->possibleDuplicatesFor($caseA->fresh());

        $this->assertCount(1, $matches);
        $this->assertSame(MatchConfidence::Confident, $matches->first()['confidence']);

        // Suggested, not performed. Two cases still stand.
        $this->assertSame(2, CandidateCase::query()->count());
    }

    public function test_a_shared_name_alone_is_only_a_possibility(): void
    {
        $first = Application::factory()->create([
            'email' => 'ada.lovelace@example.org',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
        ]);
        $second = Application::factory()->create([
            'email' => 'a.lovelace@example.net',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
        ]);

        $caseA = $this->service()->openFor($first);
        $this->service()->openFor($second);

        $matches = $this->service()->possibleDuplicatesFor($caseA->fresh());

        $this->assertCount(1, $matches);
        $this->assertSame(MatchConfidence::Possible, $matches->first()['confidence']);
    }

    public function test_joining_two_cases_moves_every_submission_onto_one(): void
    {
        $application = Application::factory()->create(['email' => 'ada@example.org']);
        $referral = Referral::factory()->create(['candidate_email' => 'ada@example.org']);

        $surviving = $this->service()->openFor($application);
        $absorbed = $this->service()->openFor($referral);

        $this->service()->join($absorbed, $surviving, User::factory()->create());

        $surviving->refresh();

        $this->assertSame(1, $surviving->applications()->count());
        $this->assertSame(1, $surviving->referrals()->count());
        $this->assertNotNull($surviving->linked_by, 'The record must show who judged them to be one person.');
        $this->assertNotNull($surviving->linked_at);
    }

    /**
     * Joining a decided case would fold a judgement into a case it was never
     * made about.
     */
    public function test_a_decided_case_cannot_be_joined_to_another(): void
    {
        $application = Application::factory()->create();
        $referral = Referral::factory()->create();

        $surviving = $this->service()->openFor($application);
        $absorbed = $this->service()->openFor($referral);

        app(ReviewService::class)->approve($application->fresh(), User::factory()->create());

        $this->expectException(DomainException::class);

        $this->service()->join($absorbed, $surviving->fresh(), User::factory()->create());
    }

    public function test_an_outstanding_invitation_blocks_a_second_one(): void
    {
        $case = CandidateCase::query()->create(['display_name' => 'Ada Lovelace', 'email' => 'ada@example.org']);

        $this->assertNull($this->service()->invitationBlocking($case));

        $case->invitations()->create([
            'email' => 'ada@example.org',
            'delivery_channel' => 'email',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'token_hash' => str_repeat('a', 64),
            'expires_at' => now()->addDays(14),
            'status' => 'issued',
            'issued_by' => User::factory()->create()->id,
        ]);

        $this->assertNotNull($this->service()->invitationBlocking($case->fresh()));
    }

    /**
     * The database, not merely the application, refuses a second live
     * invitation for one person.
     */
    public function test_the_database_itself_refuses_two_live_invitations_for_one_case(): void
    {
        $case = CandidateCase::query()->create(['display_name' => 'Ada Lovelace']);
        $issuer = User::factory()->create();

        $attributes = [
            'email' => 'ada@example.org',
            'delivery_channel' => 'email',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'expires_at' => now()->addDays(14),
            'status' => 'issued',
            'issued_by' => $issuer->id,
        ];

        $case->invitations()->create([...$attributes, 'token_hash' => str_repeat('a', 64)]);

        $this->expectException(QueryException::class);

        $case->invitations()->create([...$attributes, 'token_hash' => str_repeat('b', 64)]);
    }
}
