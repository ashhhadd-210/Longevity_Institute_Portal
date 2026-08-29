<?php

namespace App\Services\Admission;

use App\Enums\InvitationChannel;
use App\Enums\InvitationStatus;
use App\Events\InvitationIssued;
use App\Models\AdmissionDecision;
use App\Models\CandidateCase;
use App\Models\Collaborator;
use App\Models\Invitation;
use App\Models\User;
use App\Services\Governance\AuditRecorder;
use App\Support\IssuedInvitation;
use DomainException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Issuing, re-sending and revoking secure invitations (doc 02 §2).
 *
 * An invitation is a consequence of approval, never a way of judging somebody,
 * which is why nothing here decides anything.
 *
 * Two properties matter more than the rest and are enforced rather than
 * documented. The link is shown once, because only its hash is kept. And the
 * token survives being opened: LinkedIn and Outlook follow links to scan them,
 * so consuming a token on sight would burn invitations before their recipients
 * ever saw them (agreed 18 August 2026).
 */
class InvitationService
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * Issue an invitation.
     *
     * An email address is optional. Many of these relationships begin on
     * LinkedIn, where the Institute has no address for the person at all, and
     * requiring one would have made the Founder invent something. What is
     * required is a name and a channel, and the two must agree: the Portal
     * cannot send an email it has no address for.
     */
    public function issue(
        User $issuedBy,
        string $firstName,
        string $lastName,
        ?string $email = null,
        InvitationChannel $channel = InvitationChannel::Email,
        ?string $preferredName = null,
        ?string $middleName = null,
        ?AdmissionDecision $decision = null,
        ?CandidateCase $case = null,
        ?Collaborator $referrer = null,
        ?string $personalMessage = null,
    ): IssuedInvitation {
        if ($channel === InvitationChannel::Email && blank($email)) {
            throw new InvalidArgumentException('An invitation sent by the Portal needs an email address.');
        }

        if ($case !== null) {
            $this->guardNoLiveInvitation($case);
        }

        $token = Invitation::generateToken();

        $invitation = DB::transaction(function () use (
            $email, $channel, $firstName, $preferredName, $middleName, $lastName,
            $issuedBy, $decision, $case, $referrer, $personalMessage, $token,
        ): Invitation {
            $invitation = Invitation::query()->create([
                'email' => $email,
                'delivery_channel' => $channel->value,
                'first_name' => $firstName,
                'preferred_name' => $preferredName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'token_hash' => $token['hash'],
                'expires_at' => now()->addDays((int) config('portal.invitation_expiry_days')),
                'status' => InvitationStatus::Issued->value,
                'admission_decision_id' => $decision?->id,
                'candidate_case_id' => $case?->id,
                'referrer_collaborator_id' => $referrer?->id,
                'personal_message' => $personalMessage,
                'issued_by' => $issuedBy->id,
            ]);

            $this->audit->record('admission.invitation.issued', $issuedBy, $invitation, [
                'email' => $email,
                'delivery_channel' => $channel->value,
                'expires_at' => $invitation->expires_at->toIso8601String(),
                'admission_decision_id' => $decision?->id,
                'candidate_case_id' => $case?->id,
            ]);

            return $invitation;
        });

        InvitationIssued::dispatch($invitation, $token['raw']);

        return new IssuedInvitation($invitation, $token['raw'], $this->acceptanceUrl($token['raw']));
    }

    /**
     * Reissue with a fresh token and expiry; the previous link stops working
     * immediately. Provenance is preserved, so the history still shows that an
     * earlier attempt was made.
     *
     * This is the answer to a lost link, which is why it is one call and not a
     * support request.
     */
    public function reissue(Invitation $invitation, User $actor): IssuedInvitation
    {
        if (! in_array($invitation->status, [InvitationStatus::Issued, InvitationStatus::Sent, InvitationStatus::Expired], true)) {
            throw new InvalidArgumentException('Only an outstanding or lapsed invitation can be reissued.');
        }

        $token = Invitation::generateToken();

        DB::transaction(function () use ($invitation, $actor, $token): void {
            $invitation->update([
                'token_hash' => $token['hash'],
                'expires_at' => now()->addDays((int) config('portal.invitation_expiry_days')),
                'status' => InvitationStatus::Issued->value,
            ]);

            $this->audit->record('admission.invitation.reissued', $actor, $invitation, [
                'send_count' => $invitation->send_count,
                'delivery_channel' => $invitation->delivery_channel->value,
            ]);
        });

        InvitationIssued::dispatch($invitation, $token['raw'], true);

        return new IssuedInvitation($invitation, $token['raw'], $this->acceptanceUrl($token['raw']));
    }

    public function revoke(Invitation $invitation, User $actor, ?string $reason = null): Invitation
    {
        if ($invitation->status === InvitationStatus::Accepted) {
            throw new InvalidArgumentException('An accepted invitation cannot be revoked.');
        }

        DB::transaction(function () use ($invitation, $actor, $reason): void {
            $invitation->update([
                'status' => InvitationStatus::Revoked->value,
                'revoked_at' => now(),
            ]);

            $this->audit->record('admission.invitation.revoked', $actor, $invitation, [
                'reason' => $reason,
            ]);
        });

        return $invitation;
    }

    /**
     * Look an invitation up by its raw token without changing anything.
     *
     * Nothing here consumes, marks or expires the invitation, and no caller
     * should make it do so. A link scanner opening this is a normal event, not
     * an acceptance.
     */
    public function findByToken(string $rawToken): ?Invitation
    {
        return Invitation::query()
            ->where('token_hash', Invitation::hashToken($rawToken))
            ->first();
    }

    /**
     * The invitation a token points at, only if it can still be accepted.
     */
    public function findAcceptable(string $rawToken): ?Invitation
    {
        $invitation = $this->findByToken($rawToken);

        return $invitation !== null && $invitation->isAcceptable() ? $invitation : null;
    }

    /**
     * A referral or application for somebody already invited or admitted must
     * not start a second track for the same person.
     */
    private function guardNoLiveInvitation(CandidateCase $case): void
    {
        $existing = $case->liveInvitation();

        if ($existing === null) {
            return;
        }

        throw new DomainException($existing->status === InvitationStatus::Accepted
            ? 'This person has already registered and is an admitted collaborator.'
            : 'This person already has an invitation outstanding. Reissue that one rather than creating a second.');
    }

    private function acceptanceUrl(string $rawToken): string
    {
        return route('invitations.show', ['token' => $rawToken]);
    }
}
