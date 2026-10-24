<?php

namespace Tests\Feature\Admission;

use App\Enums\ApplicationStatus;
use App\Enums\InvitationStatus;
use App\Models\Application;
use App\Models\CandidateCase;
use App\Models\Collaborator;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\CreatesUsersWithRoles;
use Tests\TestCase;

/**
 * The run that closes Milestone 2: a website application, review, decision,
 * invitation, registration, and an admitted collaborator, performed entirely
 * through the interface with no developer present.
 *
 * Every step here is a page the Founder actually uses. If this test passes, the
 * milestone does what was promised.
 */
class CompleteAdmissionJourneyTest extends TestCase
{
    use CreatesUsersWithRoles;
    use RefreshDatabase;

    public function test_a_person_travels_from_the_website_form_to_admitted_collaborator(): void
    {
        Mail::fake();
        $this->seedRoles();

        $founder = $this->userWithRole('administrator', mfaConfirmed: true);

        // 1. A stranger applies through the public website. No account, no
        //    authentication, nothing known about them beforehand.
        $this->post(route('applications.store'), [
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@example.org',
            'professional_title' => 'Professor of Actuarial Science',
            'organisation_text' => 'University of Vilnius',
            'country_code' => 'LT',
            'motivation' => 'My work on pension longevity risk bears directly on these questions.',
        ])->assertRedirect(route('applications.received'));

        $application = Application::query()->firstOrFail();
        $case = CandidateCase::query()->firstOrFail();

        $this->assertSame(ApplicationStatus::Submitted, $application->status);
        $this->assertSame($case->id, $application->candidate_case_id);

        // 2. The Founder finds it waiting in the queue.
        $this->actingAs($founder)
            ->get(route('admin.queue.index'))
            ->assertOk()
            ->assertSee('Ada Lovelace');

        // 3. She opens the case and sees the whole submission, title unabridged.
        $this->actingAs($founder)
            ->get(route('admin.cases.show', $case))
            ->assertOk()
            ->assertSee('Professor of Actuarial Science')
            ->assertSee('pension longevity risk');

        // 4. She asks a question first. This is not a decision.
        $this->actingAs($founder)
            ->post(route('admin.review.information', ['application', $application->id]), [
                'required' => 'Which of your appointments is current?',
            ])->assertRedirect();

        $this->assertSame(ApplicationStatus::AwaitingInformation, $application->fresh()->status);
        $this->assertTrue($application->fresh()->status->isOpen());

        // 5. A reply arrives and she records it.
        $informationRequest = $application->fresh()->informationRequests()->firstOrFail();

        $this->actingAs($founder)
            ->post(route('admin.information.response', $informationRequest), [
                'response' => 'The Vilnius chair, held since 2019.',
            ])->assertRedirect();

        $this->assertSame(ApplicationStatus::UnderReview, $application->fresh()->status);

        // 6. She approves. This records a judgement and invites nobody.
        $this->actingAs($founder)
            ->post(route('admin.review.approve', ['application', $application->id]), [
                'rationale' => 'Exactly the interdisciplinary reach the Institute needs.',
            ])->assertRedirect();

        $this->assertSame(ApplicationStatus::Approved, $application->fresh()->status);
        $this->assertSame(0, Invitation::query()->count(), 'Approval must not invite anybody by itself.');

        // 7. She issues the invitation as a separate, deliberate act.
        $response = $this->actingAs($founder)
            ->post(route('admin.invitations.store-for-case', $case), [
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'delivery_channel' => 'email',
                'email' => 'ada@example.org',
                'personal_message' => 'I hope you will join us.',
            ]);

        $invitation = Invitation::query()->firstOrFail();
        $response->assertRedirect(route('admin.invitations.issued', $invitation));

        // 8. The link is shown to her exactly once.
        $link = session('invitation_link');
        $this->assertNotNull($link);

        $this->actingAs($founder)
            ->get(route('admin.invitations.issued', $invitation))
            ->assertOk()
            ->assertSee('only time the link is shown');

        $token = str($link)->afterLast('/')->toString();

        // 9. A link scanner opens it before Ada ever does, twice. Nothing is
        //    spent: the invitation is still acceptable and still unaccepted.
        $this->get(route('invitations.show', ['token' => $token]))->assertOk();
        $this->get(route('invitations.show', ['token' => $token]))->assertOk();

        $this->assertTrue($invitation->fresh()->isAcceptable());
        $this->assertNull($invitation->fresh()->accepted_at);

        // 10. Ada registers, confirming her own details and stating how she
        //     would like to be addressed.
        $this->post(route('invitations.accept', ['token' => $token]), [
            'first_name' => 'Ada',
            'preferred_name' => 'Addie',
            'last_name' => 'Lovelace',
            'password' => 'a-long-enough-passphrase-1',
            'password_confirmation' => 'a-long-enough-passphrase-1',
            'privacy_consent' => '1',
        ])->assertRedirect(route('home'));

        // 11. An admitted collaborator now exists, and the invitation is spent.
        $collaborator = Collaborator::query()->firstOrFail();
        $ada = User::query()->where('email', 'ada@example.org')->firstOrFail();

        $this->assertSame($ada->id, $collaborator->user_id);
        $this->assertSame($invitation->id, $collaborator->invitation_id);
        $this->assertSame(InvitationStatus::Accepted, $invitation->fresh()->status);
        $this->assertSame('Addie', $ada->salutationName());
        $this->assertNotNull($ada->email_verified_at, 'An invitation the Institute posted proves the address.');
        $this->assertNotNull($collaborator->profile);
        $this->assertSame(1, $ada->consents()->count());

        // 12. The link cannot be used a second time.
        $this->get(route('invitations.show', ['token' => $token]))
            ->assertOk()
            ->assertSee('already been used');

        // 13. Her history reads as one continuous story.
        $this->actingAs($founder)
            ->get(route('admin.cases.show', $case))
            ->assertOk()
            ->assertSee('Applied through the Institute website')
            ->assertSee('More information asked of the applicant')
            ->assertSee('Approved for admission')
            ->assertSee('Registered and admitted as a collaborator');
    }

    /**
     * The LinkedIn route she asked about: a name typed from scratch, no email
     * address anywhere, and the link sent by hand.
     */
    public function test_the_founder_can_invite_someone_from_linkedin_with_only_a_name(): void
    {
        Mail::fake();
        $this->seedRoles();

        $founder = $this->userWithRole('administrator', mfaConfirmed: true);

        $this->actingAs($founder)->get(route('admin.invitations.create'))->assertOk();

        $this->actingAs($founder)->post(route('admin.invitations.store'), [
            'first_name' => 'Khim',
            'last_name' => 'Tong',
            'delivery_channel' => 'copy_link',
            'personal_message' => 'We spoke briefly at the Chicago symposium.',
        ])->assertRedirect();

        $invitation = Invitation::query()->firstOrFail();

        $this->assertNull($invitation->email);
        $this->assertSame('copy_link', $invitation->delivery_channel->value);

        // Nothing was sent, and the Portal does not pretend otherwise.
        Mail::assertNothingSent();
        $this->assertSame(InvitationStatus::Issued, $invitation->status);
        $this->assertSame(0, $invitation->send_count);

        $token = str(session('invitation_link'))->afterLast('/')->toString();

        // The invited person supplies their own address, since the Institute
        // never had one.
        $this->get(route('invitations.show', ['token' => $token]))
            ->assertOk()
            ->assertSee('We spoke briefly at the Chicago symposium.');

        $this->post(route('invitations.accept', ['token' => $token]), [
            'first_name' => 'Khim',
            'last_name' => 'Tong',
            'email' => 'khim@example.org',
            'password' => 'a-long-enough-passphrase-1',
            'password_confirmation' => 'a-long-enough-passphrase-1',
            'privacy_consent' => '1',
        ])->assertRedirect(route('home'));

        $khim = User::query()->where('email', 'khim@example.org')->firstOrFail();

        $this->assertNotNull($khim->collaborator);

        // The address was typed in by the person, not proven by the Institute
        // having posted to it, so it is recorded as unconfirmed rather than
        // quietly trusted.
        $this->assertNull($khim->email_verified_at);
    }
}
