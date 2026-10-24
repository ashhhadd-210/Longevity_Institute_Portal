<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CollaboratorStatus;
use App\Enums\InvitationStatus;
use App\Http\Controllers\Controller;
use App\Models\Collaborator;
use App\Models\Invitation;
use App\Services\Profiles\ProfileEditor;
use Illuminate\View\View;

/**
 * Where everybody stands, from invitation to the Directory.
 *
 * The Institute asked to tell apart, at a glance, someone who has only been
 * invited, someone who has registered, someone whose profile is in progress,
 * and someone whose profile is ready. Those are the four columns of this page.
 */
class CollaboratorStatusController extends Controller
{
    public function index(ProfileEditor $editor): View
    {
        $waiting = Invitation::query()
            ->whereDoesntHave('collaborator')
            ->whereIn('status', [InvitationStatus::Issued->value, InvitationStatus::Sent->value, InvitationStatus::Expired->value])
            ->latest()
            ->get();

        $collaborators = Collaborator::query()
            ->where('status', CollaboratorStatus::Active->value)
            ->with(['user', 'profile'])
            ->latest('admitted_at')
            ->get()
            ->map(function (Collaborator $collaborator) use ($editor): array {
                $profile = $collaborator->profile;
                $missing = $profile !== null ? $editor->missingForReadiness($profile) : [];

                $stage = match (true) {
                    $profile === null || $profile->completed_at === null => 'in_progress',
                    $profile->isListable() => 'listed',
                    default => 'withheld',
                };

                return ['collaborator' => $collaborator, 'profile' => $profile, 'missing' => $missing, 'stage' => $stage];
            });

        return view('admin.collaborators.index', [
            'waiting' => $waiting,
            'inProgress' => $collaborators->where('stage', 'in_progress')->values(),
            'ready' => $collaborators->whereIn('stage', ['listed', 'withheld'])->values(),
        ]);
    }
}
