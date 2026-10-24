<?php

namespace App\Services\Expertise;

use App\Models\Discipline;
use App\Models\ExpertiseArea;
use App\Models\Profile;
use App\Models\User;
use App\Models\VocabularyProposal;
use App\Services\Governance\AuditRecorder;
use App\Services\Profiles\ProfileEditor;
use Illuminate\Support\Facades\DB;

/**
 * How a collaborator says where they belong.
 *
 * Two independent choices. Fields are the professional home, chosen directly.
 * Specialties are optional precision, and selecting one never assigns a Field.
 *
 * There is no maximum and no primary Field, because some people genuinely bring
 * more than one professional perspective. The orientation says gently that three
 * Fields describe a person better than fifteen, but nothing enforces it: a rule
 * would punish the very collaborators the Institute is unusual for wanting.
 *
 * And nothing is ever a dead end. If no term fits, the person writes their own
 * and carries on.
 */
class ProfileClassification
{
    public function __construct(
        private readonly AuditRecorder $audit,
        private readonly ProfileEditor $editor,
    ) {}

    /**
     * Replace the Fields a collaborator claims.
     *
     * @param  array<int, int>  $fieldIds
     */
    public function setFields(Profile $profile, array $fieldIds): void
    {
        $valid = Discipline::query()
            ->selectable()
            ->whereIn('id', $fieldIds)
            ->pluck('id')
            ->all();

        // Preserve the order the collaborator put them in.
        $ordered = array_values(array_filter($fieldIds, static fn (int $id): bool => in_array($id, $valid, true)));

        $profile->fields()->sync(
            collect($ordered)->mapWithKeys(fn (int $id, int $i): array => [$id => ['position' => $i]])->all()
        );

        $this->audit->record('profile.fields.set', $profile->collaborator?->user, $profile, [
            'field_ids' => $ordered,
        ]);

        // A Field is one of the things that makes a profile complete, and people
        // fill these sections in whatever order suits them. Whichever section
        // is saved last must be the one that notices the profile is finished.
        $this->editor->refreshCompletion($profile);
    }

    /**
     * Replace the Specialties a collaborator claims.
     *
     * Deliberately does not touch Fields. A Specialty relates to Fields for
     * search, and that relationship must never become an identity claim.
     *
     * @param  array<int, int>  $specialtyIds
     */
    public function setSpecialties(Profile $profile, array $specialtyIds): void
    {
        $valid = ExpertiseArea::query()
            ->selectable()
            ->whereIn('id', $specialtyIds)
            ->pluck('id')
            ->all();

        $ordered = array_values(array_filter($specialtyIds, static fn (int $id): bool => in_array($id, $valid, true)));

        $profile->specialties()->sync(
            collect($ordered)->mapWithKeys(fn (int $id, int $i): array => [$id => ['position' => $i]])->all()
        );

        $this->audit->record('profile.specialties.set', $profile->collaborator?->user, $profile, [
            'specialty_ids' => $ordered,
        ]);
    }

    /**
     * Record a term the collaborator uses when the vocabulary has nothing that
     * fits, and let them carry on immediately.
     *
     * The profile saves. Nothing waits on the Institute. The term is attached to
     * the person who wrote it and is not offered to anybody else until approved,
     * so an unreviewed word never leaks into the vocabulary.
     *
     * Proposing the same term twice simply returns the first one rather than
     * filling the Institute's queue with duplicates from one impatient person.
     */
    public function proposeTerm(Profile $profile, User $user, string $kind, string $term, ?string $meaning = null, ?string $jurisdiction = null): VocabularyProposal
    {
        $term = trim($term);

        $existing = VocabularyProposal::query()
            ->where('profile_id', $profile->id)
            ->where('kind', $kind)
            ->whereRaw('lower(term) = ?', [mb_strtolower($term)])
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        $proposal = VocabularyProposal::query()->create([
            'kind' => $kind,
            'term' => $term,
            'meaning' => $meaning,
            'jurisdiction' => $jurisdiction,
            'proposed_by_user_id' => $user->id,
            'profile_id' => $profile->id,
            'status' => VocabularyProposal::STATUS_PENDING,
        ]);

        $this->audit->record('vocabulary.proposed', $user, $proposal, [
            'kind' => $kind,
            'term' => $term,
        ]);

        // A pending Field counts towards Directory readiness.
        $this->editor->refreshCompletion($profile);

        return $proposal;
    }

    /**
     * Everything the directory should know about this collaborator's expertise.
     *
     * The Fields they claimed, plus, for search only, the Fields their
     * Specialties are recognizable within. Somebody searching Epidemiology finds
     * the person who chose Nutritional Epidemiology, without that person ever
     * being labeled an epidemiologist.
     *
     * @return array{claimed: array<int, int>, searchable: array<int, int>}
     */
    public function fieldReach(Profile $profile): array
    {
        $claimed = $profile->fields()->pluck('disciplines.id')->all();

        $viaSpecialties = DB::table('profile_specialties')
            ->join('discipline_expertise_area as link', 'link.expertise_area_id', '=', 'profile_specialties.expertise_area_id')
            ->where('profile_specialties.profile_id', $profile->id)
            ->pluck('link.discipline_id')
            ->all();

        return [
            'claimed' => array_values(array_unique($claimed)),
            'searchable' => array_values(array_unique(array_merge($claimed, $viaSpecialties))),
        ];
    }
}
