<?php

namespace Tests\Feature\Admission;

use App\Enums\InvitationChannel;
use App\Mail\ReferrerInvitationNoticeMail;
use App\Models\CandidateCase;
use App\Models\Collaborator;
use App\Models\Referral;
use App\Models\User;
use App\Services\Admission\InvitationService;
use App\Services\Admission\ReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * What a referrer hears, and everything they do not (agreed 18 August 2026).
 *
 * A referrer is told once an invitation has been sent, and at no other moment.
 * They are never told that somebody was declined or set aside, which is why
 * only one notification of this kind exists in the application.
 */
class ReferrerNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function referrer(string $email = 'referrer@example.org'): Collaborator
    {
        return Collaborator::factory()->create([
            'user_id' => User::factory()->create(['email' => $email])->id,
        ]);
    }

    public function test_the_referrer_is_told_once_an_invitation_has_been_issued(): void
    {
        Mail::fake();

        $referrer = $this->referrer();
        $case = CandidateCase::query()->create(['display_name' => 'Ada Lovelace']);

        app(InvitationService::class)->issue(
            issuedBy: User::factory()->create(),
            firstName: 'Ada',
            lastName: 'Lovelace',
            email: 'ada@example.org',
            case: $case,
            referrer: $referrer,
        );

        Mail::assertSent(ReferrerInvitationNoticeMail::class, fn (ReferrerInvitationNoticeMail $mail): bool => $mail->hasTo('referrer@example.org'));
    }

    /**
     * The copy-link route notifies the referrer too. The Institute has decided
     * to invite the person, which is what the referrer is being told.
     */
    public function test_a_copy_link_invitation_also_tells_the_referrer(): void
    {
        Mail::fake();

        $referrer = $this->referrer();
        $case = CandidateCase::query()->create(['display_name' => 'Khim Tong']);

        app(InvitationService::class)->issue(
            issuedBy: User::factory()->create(),
            firstName: 'Khim',
            lastName: 'Tong',
            channel: InvitationChannel::CopyLink,
            case: $case,
            referrer: $referrer,
        );

        Mail::assertSent(ReferrerInvitationNoticeMail::class);
    }

    public function test_a_direct_invitation_notifies_nobody(): void
    {
        Mail::fake();

        app(InvitationService::class)->issue(
            issuedBy: User::factory()->create(),
            firstName: 'Ada',
            lastName: 'Lovelace',
            email: 'ada@example.org',
        );

        Mail::assertNotSent(ReferrerInvitationNoticeMail::class);
    }

    /**
     * A lost link is answered by reissuing. That must not send the referrer a
     * second message congratulating them on the same introduction.
     */
    public function test_reissuing_a_link_does_not_tell_the_referrer_again(): void
    {
        Mail::fake();

        $referrer = $this->referrer();
        $case = CandidateCase::query()->create(['display_name' => 'Ada Lovelace']);

        $issued = app(InvitationService::class)->issue(
            issuedBy: User::factory()->create(),
            firstName: 'Ada',
            lastName: 'Lovelace',
            email: 'ada@example.org',
            case: $case,
            referrer: $referrer,
        );

        app(InvitationService::class)->reissue($issued->invitation, User::factory()->create());

        Mail::assertSent(ReferrerInvitationNoticeMail::class, 1);
    }

    /**
     * The silence that matters most. A referrer must never learn that the
     * person they recommended was turned away.
     */
    public function test_a_referrer_is_never_told_about_a_decline_or_a_pause(): void
    {
        Mail::fake();

        $referrer = $this->referrer();
        $declined = Referral::factory()->create(['referrer_collaborator_id' => $referrer->id]);
        $paused = Referral::factory()->create(['referrer_collaborator_id' => $referrer->id]);

        app(ReviewService::class)->decline($declined, User::factory()->create(), 'Outside scope.');
        app(ReviewService::class)->setAside($paused, User::factory()->create(), 'Later.');

        Mail::assertNothingSent();
    }
}
