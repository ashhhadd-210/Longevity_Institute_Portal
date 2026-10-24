<?php

namespace Tests\Feature\Admission;

use App\Enums\InvitationStatus;
use App\Models\Application;
use App\Models\AuditEvent;
use App\Models\CandidateCase;
use App\Models\Invitation;
use App\Models\Role;
use App\Models\User;
use App\Services\Admission\CandidateCaseService;
use App\Services\Admission\CaseHistoryService;
use App\Services\Admission\ReviewService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * Gaps found by auditing the built system against everything promised, and the
 * behaviour that closes them.
 */
class AuditFollowUpTest extends TestCase
{
    use RefreshDatabase;

    /**
     * An invitation is unusable the moment its expiry passes. Until it is also
     * *marked* lapsed, the dashboard keeps counting it as outstanding and the
     * candidate's history can never say it went unused, which reads exactly
     * like somebody still making up their mind.
     */
    public function test_lapsed_invitations_are_marked_so_the_record_reads_truthfully(): void
    {
        $live = Invitation::factory()->create(['expires_at' => now()->addDays(5)]);
        $lapsed = Invitation::factory()->create(['expires_at' => now()->subDay()]);

        Artisan::call('portal:expire-invitations');

        $this->assertSame(InvitationStatus::Expired, $lapsed->fresh()->status);
        $this->assertSame(InvitationStatus::Issued, $live->fresh()->status);

        $this->assertTrue(
            AuditEvent::query()->where('event_type', 'admission.invitation.lapsed')->exists()
        );
    }

    public function test_an_accepted_invitation_is_never_marked_lapsed(): void
    {
        $accepted = Invitation::factory()->accepted()->create(['expires_at' => now()->subDay()]);

        Artisan::call('portal:expire-invitations');

        $this->assertSame(InvitationStatus::Accepted, $accepted->fresh()->status);
    }

    public function test_a_lapsed_invitation_appears_in_the_candidate_history(): void
    {
        $case = CandidateCase::query()->create(['display_name' => 'Ada Lovelace']);

        Invitation::factory()->create([
            'candidate_case_id' => $case->id,
            'expires_at' => now()->subDay(),
        ]);

        Artisan::call('portal:expire-invitations');

        $summaries = app(CaseHistoryService::class)->for($case->fresh())
            ->map(fn ($entry): string => $entry->summary);

        $this->assertContains('Invitation lapsed without being used', $summaries->all());
    }

    /**
     * Offering to join a case that has already been judged would invite a
     * refusal. The panel should explain instead.
     */
    public function test_a_match_that_is_already_settled_explains_itself_rather_than_offering_a_join(): void
    {
        $cases = app(CandidateCaseService::class);

        $mine = $cases->openFor(Application::factory()->create([
            'email' => 'ada@example.org', 'first_name' => 'Ada', 'last_name' => 'Lovelace',
        ]));

        $theirs = $cases->openFor(Application::factory()->create([
            'email' => 'ada@example.org', 'first_name' => 'Ada', 'last_name' => 'Lovelace',
        ]));

        app(ReviewService::class)->approve($theirs->applications()->first(), User::factory()->create());

        $match = $cases->possibleDuplicatesFor($mine->fresh())->first();

        $this->assertNotNull($match->settled);
        $this->assertStringContainsString('already been decided', $match->settled);
    }

    public function test_an_unsettled_match_still_offers_the_join(): void
    {
        $cases = app(CandidateCaseService::class);

        $mine = $cases->openFor(Application::factory()->create([
            'email' => 'ada@example.org', 'first_name' => 'Ada', 'last_name' => 'Lovelace',
        ]));
        $cases->openFor(Application::factory()->create([
            'email' => 'ada@example.org', 'first_name' => 'Ada', 'last_name' => 'Lovelace',
        ]));

        $this->assertTrue($cases->possibleDuplicatesFor($mine->fresh())->first()->isJoinable());
    }

    /**
     * A generational suffix belongs beside the family name, not inside it, so
     * that sorting keeps a person next to their own namesake.
     */
    public function test_a_generational_suffix_is_held_apart_from_the_family_name(): void
    {
        $this->seed(RolePermissionSeeder::class);
        Role::query()->firstOrCreate(['name' => 'collaborator'], ['requires_mfa' => false]);

        $token = Invitation::generateToken();
        Invitation::factory()->create([
            'token_hash' => $token['hash'],
            'email' => 'howard@example.org',
        ]);

        $this->post(route('invitations.accept', ['token' => $token['raw']]), [
            'first_name' => 'Howard',
            'last_name' => 'Rutherford',
            'name_suffix' => 'III',
            'password' => 'a-long-enough-passphrase-1',
            'password_confirmation' => 'a-long-enough-passphrase-1',
            'privacy_consent' => '1',
        ])->assertRedirect(route('home'));

        $user = User::query()->where('email', 'howard@example.org')->firstOrFail();

        $this->assertSame('III', $user->name_suffix);
        $this->assertSame('Rutherford', $user->last_name, 'The suffix must not contaminate the family name.');
        $this->assertSame('Howard Rutherford III', $user->fullName());
    }
}
