<?php

namespace App\Console\Commands;

use App\Enums\InvitationStatus;
use App\Models\Invitation;
use App\Services\Governance\AuditRecorder;
use Illuminate\Console\Command;

/**
 * Marks invitations that have passed their expiry date.
 *
 * Nothing about security depends on this: an invitation is already unusable the
 * moment its expiry passes, because acceptance checks the date rather than the
 * status. What depends on it is the truth of what the Institute is shown.
 *
 * Without this sweep a lapsed invitation still reads as outstanding on the
 * dashboard, and a candidate's history can never say that an invitation was
 * never taken up, which is precisely the sort of thing the Founder needs to
 * notice. Silence about a lapsed invitation looks identical to a person still
 * making up their mind.
 */
class ExpireInvitations extends Command
{
    protected $signature = 'portal:expire-invitations';

    protected $description = 'Mark invitations whose expiry date has passed, so the record reads truthfully';

    public function handle(AuditRecorder $audit): int
    {
        $lapsed = Invitation::query()
            ->whereIn('status', [InvitationStatus::Issued->value, InvitationStatus::Sent->value])
            ->where('expires_at', '<', now())
            ->get();

        foreach ($lapsed as $invitation) {
            $invitation->update(['status' => InvitationStatus::Expired->value]);

            $audit->record('admission.invitation.lapsed', null, $invitation, [
                'expired_at' => $invitation->expires_at->toIso8601String(),
                'delivery_channel' => $invitation->delivery_channel->value,
            ]);
        }

        $this->components->info($lapsed->isEmpty()
            ? 'No invitations have lapsed.'
            : $lapsed->count().' '.str('invitation')->plural($lapsed->count()).' marked as lapsed.');

        return self::SUCCESS;
    }
}
