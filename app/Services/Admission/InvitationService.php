<?php

namespace App\Services\Admission;

use App\Enums\InvitationStatus;
use App\Events\InvitationIssued;
use App\Models\AdmissionDecision;
use App\Models\Invitation;
use App\Models\User;
use App\Services\Governance\AuditRecorder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Issuing, re-sending and revoking secure invitations (doc 02 §2). The web
 * interface consumes this in Milestone 2; an API could consume it later
 * without the rules being reimplemented (doc 01 §2).
 */
class InvitationService
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * Issue an invitation, optionally linked to the admission decision that
     * authorised it (application/referral pathways) or decision-less context
     * being a direct invitation recorded through a decision with no subject.
     */
    public function issue(
        string $email,
        string $firstName,
        string $lastName,
        User $issuedBy,
        ?AdmissionDecision $decision = null,
    ): Invitation {
        $token = Invitation::generateToken();

        $invitation = DB::transaction(function () use ($email, $firstName, $lastName, $issuedBy, $decision, $token): Invitation {
            $invitation = Invitation::query()->create([
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'token_hash' => $token['hash'],
                'expires_at' => now()->addDays((int) config('portal.invitation_expiry_days')),
                'status' => InvitationStatus::Issued->value,
                'admission_decision_id' => $decision?->id,
                'issued_by' => $issuedBy->id,
            ]);

            $this->audit->record('admission.invitation.issued', $issuedBy, $invitation, [
                'email' => $email,
                'expires_at' => $invitation->expires_at->toIso8601String(),
                'admission_decision_id' => $decision?->id,
            ]);

            return $invitation;
        });

        InvitationIssued::dispatch($invitation, $token['raw']);

        return $invitation;
    }

    /**
     * Re-send with a fresh token and expiry; the previous token stops working
     * immediately. Provenance (decision linkage, send history) is preserved.
     */
    public function resend(Invitation $invitation, User $actor): Invitation
    {
        if (! in_array($invitation->status, [InvitationStatus::Issued, InvitationStatus::Sent, InvitationStatus::Expired], true)) {
            throw new InvalidArgumentException('Only outstanding or expired invitations can be re-sent.');
        }

        $token = Invitation::generateToken();

        DB::transaction(function () use ($invitation, $actor, $token): void {
            $invitation->update([
                'token_hash' => $token['hash'],
                'expires_at' => now()->addDays((int) config('portal.invitation_expiry_days')),
                'status' => InvitationStatus::Issued->value,
            ]);

            $this->audit->record('admission.invitation.resent', $actor, $invitation, [
                'send_count' => $invitation->send_count,
            ]);
        });

        InvitationIssued::dispatch($invitation, $token['raw'], true);

        return $invitation;
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
     * Find an acceptable invitation by its raw token — hashed lookup,
     * constant-length comparison, status and expiry checked.
     */
    public function findAcceptable(string $rawToken): ?Invitation
    {
        $invitation = Invitation::query()
            ->where('token_hash', Invitation::hashToken($rawToken))
            ->first();

        return $invitation !== null && $invitation->isAcceptable() ? $invitation : null;
    }
}
