<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Credential;
use App\Models\Discipline;
use App\Models\ExpertiseArea;
use App\Models\VocabularyProposal;
use App\Services\Expertise\CredentialLibrary;
use App\Services\Expertise\ExpertiseSearch;
use App\Services\Expertise\VocabularyReview;
use App\Support\ExpertiseMatch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

/**
 * The Institute deciding what joins its vocabulary.
 *
 * One queue for Fields, Specialties and credentials, because they follow the
 * same lifecycle and the Founder should meet them in one place. Each proposal
 * arrives with the near matches the vocabulary already holds, so mapping to an
 * existing term is one click and approving a duplicate is hard to do by
 * accident.
 *
 * At Alpha this queue will hold a handful of items and be read by eye. It is
 * built to still make sense at a few hundred.
 */
class VocabularyController extends Controller
{
    public function __construct(
        private readonly VocabularyReview $review,
        private readonly ExpertiseSearch $expertise,
        private readonly CredentialLibrary $credentials,
    ) {}

    public function index(): View
    {
        $pending = VocabularyProposal::query()
            ->awaitingReview()
            ->with(['proposedBy', 'profile.collaborator.user'])
            ->get();

        $suggestions = $pending->mapWithKeys(fn (VocabularyProposal $p): array => [
            $p->id => $this->suggestionsFor($p),
        ]);

        return view('admin.vocabulary.index', [
            'pending' => $pending->groupBy('kind'),
            'suggestions' => $suggestions,
            'fields' => Discipline::query()->selectable()->get(['id', 'name']),
            'recentlySettled' => VocabularyProposal::query()
                ->whereNot('status', VocabularyProposal::STATUS_PENDING)
                ->latest('reviewed_at')
                ->limit(15)
                ->get(),
        ]);
    }

    public function approve(Request $request, VocabularyProposal $proposal): RedirectResponse
    {
        $validated = $request->validate([
            'term' => ['nullable', 'string', 'max:300'],
            'meaning' => ['nullable', 'string', 'max:300'],
            'issuing_body' => ['nullable', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:600'],
            'field_ids' => ['array'],
            'field_ids.*' => ['integer', 'exists:disciplines,id'],
        ]);

        $reviewer = $request->user();
        abort_if($reviewer === null, 403);

        try {
            $this->review->approve(
                $proposal,
                $reviewer,
                filled($validated['term'] ?? null) ? $validated['term'] : null,
                filled($validated['meaning'] ?? null) ? $validated['meaning'] : null,
                array_map('intval', $validated['field_ids'] ?? []),
                filled($validated['issuing_body'] ?? null) ? $validated['issuing_body'] : null,
                filled($validated['description'] ?? null) ? $validated['description'] : null,
            );
        } catch (InvalidArgumentException $e) {
            return redirect()->route('admin.vocabulary.index')->withErrors(['proposal' => $e->getMessage()]);
        }

        return redirect()->route('admin.vocabulary.index')
            ->with('status', 'Approved. It is now part of the vocabulary and on the collaborator\'s profile.');
    }

    public function decline(Request $request, VocabularyProposal $proposal): RedirectResponse
    {
        $validated = $request->validate(['note' => ['nullable', 'string', 'max:2000']]);

        $reviewer = $request->user();
        abort_if($reviewer === null, 403);

        try {
            $this->review->decline($proposal, $reviewer, $validated['note'] ?? null);
        } catch (InvalidArgumentException $e) {
            return redirect()->route('admin.vocabulary.index')->withErrors(['proposal' => $e->getMessage()]);
        }

        return redirect()->route('admin.vocabulary.index')
            ->with('status', 'Declined. The collaborator has been told, with your note if you wrote one.');
    }

    public function map(Request $request, VocabularyProposal $proposal): RedirectResponse
    {
        $validated = $request->validate(['target_id' => ['required', 'integer']]);

        $reviewer = $request->user();
        abort_if($reviewer === null, 403);

        $target = match ($proposal->kind) {
            VocabularyProposal::KIND_FIELD => Discipline::query()->find($validated['target_id']),
            VocabularyProposal::KIND_SPECIALTY => ExpertiseArea::query()->find($validated['target_id']),
            default => Credential::query()->find($validated['target_id']),
        };

        if ($target === null) {
            return redirect()->route('admin.vocabulary.index')->withErrors(['proposal' => 'That term no longer exists.']);
        }

        try {
            $this->review->mapTo($proposal, $reviewer, $target);
        } catch (InvalidArgumentException $e) {
            return redirect()->route('admin.vocabulary.index')->withErrors(['proposal' => $e->getMessage()]);
        }

        return redirect()->route('admin.vocabulary.index')
            ->with('status', 'Matched to an existing term, which is now on the collaborator\'s profile.');
    }

    /**
     * What the vocabulary already holds that resembles this proposal. Shown so
     * that mapping is one click and duplicates are hard to approve by accident.
     *
     * @return array<int, array{id: int, label: string}>
     */
    private function suggestionsFor(VocabularyProposal $proposal): array
    {
        if ($proposal->kind === VocabularyProposal::KIND_CREDENTIAL) {
            return $this->credentials->nearMatches($proposal->term, (string) $proposal->meaning)
                ->map(fn (Credential $c): array => ['id' => $c->id, 'label' => $c->acronym.' — '.$c->describe()])
                ->all();
        }

        $onlyFields = $proposal->kind === VocabularyProposal::KIND_FIELD;

        return $this->expertise->search($proposal->term, 6)
            ->filter(fn (ExpertiseMatch $m): bool => $m->isField() === $onlyFields)
            ->map(fn (ExpertiseMatch $m): array => ['id' => $m->id, 'label' => $m->name])
            ->values()
            ->all();
    }
}
