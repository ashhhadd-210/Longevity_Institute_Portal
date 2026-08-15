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
 * invitation marked accepted and the admission recorded (doc 05 §2). The M2
 * registration interface calls this; it is built and tested in M1 because it
 * is foundation, not surface.
 */
class RegistrationService
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * @param  array{first_name: string, middle_name?: string|null, last_name: string, password: string, privacy_consent: bool, ip_address?: string|null}  $data
     */
    public function register(Invitation $invitation, array $data): Collaborator
    {
        if (! $invitation->isAcceptable()) {
            throw new InvalidArgumentException('This invitation is no longer valid.');
        }

        if ($data['privacy_consent'] !== true) {
            throw new InvalidArgumentException('Registration requires consent to the privacy notice.');
        }

        $collaborator = DB::transaction(function () use ($invitation, $data): Collaborator {
            $user = User::query()->create([
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'],
                'email' => $invitation->email,
                'password' => $data['password'],
            ]);

            // The accepted invitation itself proves control of the address —
            // no second verification round-trip (doc 05 §2).
            $user->forceFill(['email_verified_at' => now()])->save();

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

            $this->audit->record('admission.invitation.accepted', $user, $invitation);
            $this->audit->record('admission.collaborator.admitted', $user, $collaborator, [
                'invitation_id' => $invitation->id,
            ]);

            return $collaborator;
        });

        CollaboratorAdmitted::dispatch($collaborator);

        return $collaborator;
    }
}
