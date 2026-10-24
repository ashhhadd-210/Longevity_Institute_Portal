<?php

namespace App\Http\Controllers;

use App\Models\Discipline;
use App\Models\ExpertiseArea;
use App\Models\Profile;
use App\Models\VocabularyProposal;
use App\Services\Expertise\ProfileClassification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Where a collaborator says which Fields and Specialties describe them.
 *
 * The orientation is shown the first time and then gets out of the way. Its
 * most useful line is the one telling somebody they are not being asked to
 * describe everything they know: the commonest failure in classification is not
 * that people cannot find a term, it is that they try to be comprehensive and
 * end up describing nobody in particular.
 */
class ProfileClassificationController extends Controller
{
    public function __construct(private readonly ProfileClassification $classification) {}

    public function edit(Request $request): View
    {
        $profile = $this->profileFor($request);

        return view('collaborator.classification', [
            'profile' => $profile,
            'fields' => $profile->fields()->get(),
            'specialties' => $profile->specialties()->get(),
            'proposals' => $profile->vocabularyProposals()
                ->whereIn('kind', [VocabularyProposal::KIND_FIELD, VocabularyProposal::KIND_SPECIALTY])
                ->latest()
                ->get(),
            // Shown automatically the first time, and reachable afterwards
            // without interrupting anybody.
            'showOrientation' => $request->user()?->classification_oriented_at === null,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'field_ids' => ['array'],
            'field_ids.*' => ['integer', 'exists:disciplines,id'],
            'specialty_ids' => ['array'],
            'specialty_ids.*' => ['integer', 'exists:expertise_areas,id'],
        ]);

        $profile = $this->profileFor($request);

        $this->classification->setFields($profile, array_map('intval', $validated['field_ids'] ?? []));
        $this->classification->setSpecialties($profile, array_map('intval', $validated['specialty_ids'] ?? []));

        // Having been through it once, the orientation stops appearing.
        $request->user()?->forceFill(['classification_oriented_at' => now()])->save();

        return redirect()
            ->route('profile.classification.edit')
            ->with('status', 'Your Fields and Specialties have been saved.');
    }

    /**
     * The term somebody uses when nothing in the vocabulary fits. Their profile
     * is already saved; this simply records what they told us and lets them
     * carry on.
     */
    public function propose(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kind' => ['required', 'in:field,specialty'],
            'term' => ['required', 'string', 'max:300'],
        ]);

        $user = $request->user();
        abort_if($user === null, 403);

        $this->classification->proposeTerm(
            $this->profileFor($request),
            $user,
            $validated['kind'],
            $validated['term'],
        );

        return redirect()
            ->route('profile.classification.edit')
            ->with('status', 'Thank you. The Institute will consider that term, and nothing is waiting on it.');
    }

    private function profileFor(Request $request): Profile
    {
        $collaborator = $request->user()?->collaborator;

        abort_if($collaborator === null, 403, 'Only admitted collaborators have a profile.');

        return $collaborator->profile()->firstOrCreate([]);
    }

    /**
     * Used by the view to describe a Specialty's reach without implying that
     * the collaborator belongs to those Fields.
     */
    public static function reachOf(ExpertiseArea $specialty): string
    {
        return $specialty->disciplines->pluck('name')->implode(', ');
    }

    public static function describeField(Discipline $field): ?string
    {
        return $field->descriptor;
    }
}
