<?php

namespace App\Services\Admission;

use App\Enums\InvitationStatus;
use App\Events\CollaboratorAdmitted;
use App\Models\Collaborator;
use App\Models\Invitation;
use App\Models\Role;
use App\Models\User;
use App\Services\Governance\AuditRecorder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Consumes an accepted invitation and creates the admitted collaborator
 * atomically: User + Collaborator + empty Profile + consent + role, with the
 * invitation marked accepted and the admission recorded (doc 05 §2).
 *
 * This is the only place an invitation is spent. Opening the link does not
 * spend it, because LinkedIn and Outlook open links to scan them and would
 * otherwise burn invitations before their recipients ever clicked.
 */
class RegistrationService
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * @param  array{first_name: string, preferred_name?: string|null, middle_name?: string|null, last_name: string, name_suffix?: string|null, email?: string|null, password: string, privacy_consent: bool, ip_address?: string|null}  $data
     */
    public function register(Invitation $invitation, array $data): Collaborator
    {
        if (! $invitation->isAcceptable()) {
            throw new InvalidArgumentException('This invitation is no longer valid.');
        }

        if ($data['privacy_consent'] !== true) {
            throw new InvalidArgumentException('Registration requires consent to the privacy notice.');
        }

        // An invitation the Institute posted carries the address it was sent
        // to. One the Founder pasted into LinkedIn carries no address at all,
        // so the person supplies their own here.
        $email = $invitation->email ?? ($data['email'] ?? null);

        if (blank($email)) {
            throw new InvalidArgumentException('An email address is required to create an account.');
        }

        // Receiving an invitation at an address the Institute chose proves
        // control of that address, so no second round-trip is needed (doc 05
        // §2). An address the person typed in themselves proves nothing of the
        // kind, and is recorded as unconfirmed rather than quietly trusted.
        $addressWasProven = $invitation->email !== null;

        $collaborator = DB::transaction(function () use ($invitation, $data, $email, $addressWasProven): Collaborator {
            $user = User::query()->create([
                'first_name' => $data['first_name'],
                'preferred_name' => $data['preferred_name'] ?? null,
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'],
                'name_suffix' => $data['name_suffix'] ?? null,
                'email' => $email,
                'password' => $data['password'],
            ]);

            if ($addressWasProven) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }

            $user->roleAssignments()->create([
                'role_id' => Role::query()->where('name', 'collaborator')->firstOrFail()->id,
                'assigned_at' => now(),
            ]);

            $user->consents()->create([
                'document' => 'privacy_notice',
                'version' => (string) config('portal.privacy_notice_version'),
                'granted_at' => now(),
                'ip_address' => $data['ip_address'] ?? null,
            ]);

            $invitation->update([
                'status' => InvitationStatus::Accepted->value,
                'accepted_at' => now(),
            ]);

            $collaborator = Collaborator::query()->create([
                'user_id' => $user->id,
                'invitation_id' => $invitation->id,
                'admitted_at' => now(),
            ]);

            $collaborator->profile()->create([]);

            $this->audit->record('admission.invitation.accepted', $user, $invitation, [
                'delivery_channel' => $invitation->delivery_channel->value,
                'email_address_proven_by_invitation' => $addressWasProven,
            ]);

            $this->audit->record('admission.collaborator.admitted', $user, $collaborator, [
                'invitation_id' => $invitation->id,
            ]);

            return $collaborator;
        });

        CollaboratorAdmitted::dispatch($collaborator);

        return $collaborator;
    }
}
