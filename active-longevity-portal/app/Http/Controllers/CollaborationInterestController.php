<?php

namespace App\Http\Controllers;

use App\Enums\CollaboratorStatus;
use App\Models\CollaborationInterest;
use App\Models\Profile;
use App\Services\Governance\AuditRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Registering an interest in working with another collaborator.
 *
 * Offered only where the other person has said they are open to approaches,
 * never to oneself, and once per pair. The Institute sees every one and, at
 * Alpha, passes it on by hand. A record and a note, not a conversation.
 */
class CollaborationInterestController extends Controller
{
    public function __construct(private readonly AuditRecorder $audit) {}

    public function store(Request $request, Profile $profile): RedirectResponse
    {
        $validated = $request->validate(['note' => ['nullable', 'string', 'max:1000']]);

        $from = $request->user()?->collaborator?->profile;
        abort_if($from === null, 403, 'Only admitted collaborators may register an interest.');

        // The target must be somebody the directory would show, or we have
        // revealed that a hidden profile exists.
        $target = $profile->collaborator;
        abort_unless(
            $profile->completed_at !== null
            && $target !== null
            && $target->directory_visible
            && $target->status === CollaboratorStatus::Active,
            404,
        );

        if ($from->id === $profile->id) {
            return redirect()->route('profile.show', $profile);
        }

        if (! $profile->accepts_collaboration_interest) {
            return redirect()->route('profile.show', $profile)
                ->withErrors(['note' => 'This collaborator is not currently taking approaches through the Portal.']);
        }

        $interest = CollaborationInterest::query()->firstOrCreate(
            ['from_profile_id' => $from->id, 'to_profile_id' => $profile->id],
            ['note' => filled($validated['note'] ?? null) ? trim((string) $validated['note']) : null],
        );

        if ($interest->wasRecentlyCreated) {
            $this->audit->record('collaboration.interest.registered', $request->user(), $interest, [
                'to_profile_id' => $profile->id,
            ]);
        }

        return redirect()->route('profile.show', $profile)
            ->with('status', 'Thank you. The Institute has your interest and will pass it on.');
    }
}
