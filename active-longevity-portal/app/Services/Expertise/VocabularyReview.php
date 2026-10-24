<?php

namespace App\Services\Expertise;

use App\Mail\VocabularyDecidedMail;
use App\Mail\VocabularyDeclinedMail;
use App\Models\Credential;
use App\Models\Discipline;
use App\Models\ExpertiseArea;
use App\Models\User;
use App\Models\VocabularyProposal;
use App\Services\Governance\AuditRecorder;
use App\Services\Profiles\ProfileEditor;
use App\Support\VocabularyNormaliser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * The Institute deciding what joins its vocabulary.
 *
 * One mechanism for Fields, Specialties and credentials, because all three
 * follow the identical lifecycle. A proposal can be approved, in which case the
 * term joins the controlled vocabulary and appears on the proposer's profile;
 * declined, in which case it stays as the proposer's own words and nothing
 * else; or mapped, when it turns out to mean something the vocabulary already
 * holds under different wording.
 *
 * Approval may standardize the wording first. Because collaborators select
 * entries rather than typing text, the Institute's wording is what every
 * profile shows, including the profile of the person who proposed it.
 *
 * Every decision is final and recorded. A settled proposal is never quietly
 * re-decided.
 */
class VocabularyReview
{
    public function __construct(
        private readonly AuditRecorder $audit,
        private readonly ProfileEditor $editor,
    ) {}

    /**
     * Approve, optionally under standardized wording.
     *
     * @param  array<int, int>  $fieldIds  For a Specialty: the Fields it is recognizable within, for search.
     */
    public function approve(VocabularyProposal $proposal, User $reviewer, ?string $standardisedTerm = null, ?string $standardisedMeaning = null, array $fieldIds = [], ?string $issuingBody = null, ?string $description = null): VocabularyProposal
    {
        $this->guardUnsettled($proposal);

        $term = trim($standardisedTerm ?? $proposal->term);
        $meaning = trim($standardisedMeaning ?? (string) $proposal->meaning);

        DB::transaction(function () use ($proposal, $reviewer, $term, $meaning, $fieldIds, $issuingBody, $description): void {
            $resolved = match ($proposal->kind) {
                VocabularyProposal::KIND_FIELD => $this->approveField($proposal, $term),
                VocabularyProposal::KIND_SPECIALTY => $this->approveSpecialty($proposal, $term, $fieldIds),
                VocabularyProposal::KIND_CREDENTIAL => $this->approveCredential($proposal, $reviewer, $term, $meaning, $issuingBody, $description),
                default => throw new InvalidArgumentException('Unknown proposal kind.'),
            };

            $proposal->forceFill([
                'status' => VocabularyProposal::STATUS_APPROVED,
                'reviewed_by_user_id' => $reviewer->id,
                'reviewed_at' => now(),
                ...$resolved,
            ])->save();
        });

        $this->audit->record('vocabulary.approved', $reviewer, $proposal, [
            'kind' => $proposal->kind,
            'as' => $term,
            'standardized' => $term !== $proposal->term,
        ]);

        $proposal = $proposal->fresh() ?? $proposal;
        $this->tellProposer($proposal, $term);

        return $proposal;
    }

    public function decline(VocabularyProposal $proposal, User $reviewer, ?string $note = null): VocabularyProposal
    {
        $this->guardUnsettled($proposal);

        $proposal->forceFill([
            'status' => VocabularyProposal::STATUS_DECLINED,
            'reviewed_by_user_id' => $reviewer->id,
            'reviewed_at' => now(),
            'review_note' => $note,
        ])->save();

        $this->audit->record('vocabulary.declined', $reviewer, $proposal, ['kind' => $proposal->kind]);

        // The collaborator is told, with the Institute's note if one was
        // written (the Institute's decision, October 2026).
        if ($proposal->proposedBy !== null) {
            Mail::to($proposal->proposedBy->email)->queue(new VocabularyDeclinedMail($proposal));
        }

        // A pending Field counted towards readiness; once declined it does not.
        if ($proposal->profile !== null) {
            $this->editor->refreshCompletion($proposal->profile);
        }

        return $proposal;
    }

    /**
     * The proposed term already exists under other wording. Attach the existing
     * entry to the proposer's profile and settle the proposal.
     */
    public function mapTo(VocabularyProposal $proposal, User $reviewer, Discipline|ExpertiseArea|Credential $target): VocabularyProposal
    {
        $this->guardUnsettled($proposal);

        $expectedKind = match (true) {
            $target instanceof Discipline => VocabularyProposal::KIND_FIELD,
            $target instanceof ExpertiseArea => VocabularyProposal::KIND_SPECIALTY,
            default => VocabularyProposal::KIND_CREDENTIAL,
        };

        if ($expectedKind !== $proposal->kind) {
            throw new InvalidArgumentException('That is not the same kind of term as the proposal.');
        }

        DB::transaction(function () use ($proposal, $reviewer, $target): void {
            $this->attachToProposer($proposal, $target);

            $proposal->forceFill([
                'status' => VocabularyProposal::STATUS_MAPPED,
                'reviewed_by_user_id' => $reviewer->id,
                'reviewed_at' => now(),
                'resolved_discipline_id' => $target instanceof Discipline ? $target->id : null,
                'resolved_expertise_area_id' => $target instanceof ExpertiseArea ? $target->id : null,
                'resolved_credential_id' => $target instanceof Credential ? $target->id : null,
            ])->save();
        });

        $this->audit->record('vocabulary.mapped', $reviewer, $proposal, [
            'kind' => $proposal->kind,
            'to' => $target->getKey(),
        ]);

        $shownAs = $target instanceof Credential ? $target->acronym : $target->name;
        $this->tellProposer($proposal->fresh() ?? $proposal, $shownAs);

        return $proposal;
    }

    /**
     * The person who proposed a term will be wondering what became of it. When
     * it is settled in their favor, a short message says so. A decline sends
     * nothing automatically; whether to explain one is the Institute's call.
     */
    private function tellProposer(VocabularyProposal $proposal, string $shownAs): void
    {
        $proposer = $proposal->proposedBy;

        if ($proposer === null) {
            return;
        }

        Mail::to($proposer->email)->queue(new VocabularyDecidedMail(
            $proposal,
            VocabularyDecidedMail::greetingFor($proposal),
            $shownAs,
        ));
    }

    /** @return array<string, int> */
    private function approveField(VocabularyProposal $proposal, string $term): array
    {
        $field = Discipline::query()->firstOrCreate(
            ['slug' => Str::slug($term)],
            ['name' => $term, 'search_key' => VocabularyNormaliser::key($term), 'active' => true, 'sort_order' => 999],
        );

        $this->attachToProposer($proposal, $field);

        return ['resolved_discipline_id' => $field->id];
    }

    /**
     * @param  array<int, int>  $fieldIds
     * @return array<string, int>
     */
    private function approveSpecialty(VocabularyProposal $proposal, string $term, array $fieldIds): array
    {
        $specialty = ExpertiseArea::query()->firstOrCreate(
            ['slug' => Str::slug($term)],
            ['name' => $term, 'search_key' => VocabularyNormaliser::key($term), 'active' => true, 'sort_order' => 999],
        );

        if ($fieldIds !== []) {
            $specialty->disciplines()->syncWithoutDetaching(array_map('intval', $fieldIds));
        }

        $this->attachToProposer($proposal, $specialty);

        return ['resolved_expertise_area_id' => $specialty->id];
    }

    /** @return array<string, int> */
    private function approveCredential(VocabularyProposal $proposal, User $reviewer, string $acronym, string $meaning, ?string $issuingBody = null, ?string $description = null): array
    {
        $credential = Credential::query()->firstOrCreate(
            [
                'acronym' => $acronym,
                'meaning' => $meaning,
                'jurisdiction' => $proposal->jurisdiction,
            ],
            [
                'issuing_body' => filled($issuingBody) ? trim((string) $issuingBody) : null,
                'description' => filled($description) ? trim((string) $description) : null,
                'approved' => true,
                'approved_by_user_id' => $reviewer->id,
                'approved_at' => now(),
                'proposed_by_user_id' => $proposal->proposed_by_user_id,
                'active' => true,
            ],
        );

        if (! $credential->approved) {
            $credential->forceFill([
                'approved' => true,
                'approved_by_user_id' => $reviewer->id,
                'approved_at' => now(),
            ])->save();
        }

        // The library entry already existed but had never been explained.
        // What the reviewer wrote fills the gap; what was there is kept.
        $fill = [];
        if (blank($credential->issuing_body) && filled($issuingBody)) {
            $fill['issuing_body'] = trim((string) $issuingBody);
        }
        if (blank($credential->description) && filled($description)) {
            $fill['description'] = trim((string) $description);
        }
        if ($fill !== []) {
            $credential->forceFill($fill)->save();
        }

        $this->attachToProposer($proposal, $credential);

        return ['resolved_credential_id' => $credential->id];
    }

    /**
     * Put the settled term onto the profile of the person who asked for it, so
     * approval is something they see rather than something they hear about.
     * A fourth credential is left unattached rather than refused, since the
     * profile already shows the three the person chose.
     */
    private function attachToProposer(VocabularyProposal $proposal, Discipline|ExpertiseArea|Credential $target): void
    {
        $profile = $proposal->profile;

        if ($profile === null) {
            return;
        }

        if ($target instanceof Discipline) {
            $next = (int) $profile->fields()->max('profile_fields.position') + 1;
            $profile->fields()->syncWithoutDetaching([$target->id => ['position' => $next]]);

            // Gaining a Field may be the thing that completes the profile.
            $this->editor->refreshCompletion($profile);

            return;
        }

        if ($target instanceof ExpertiseArea) {
            $next = (int) $profile->specialties()->max('profile_specialties.position') + 1;
            $profile->specialties()->syncWithoutDetaching([$target->id => ['position' => $next]]);

            return;
        }

        if ($profile->approvedCredentials()->count() >= CredentialLibrary::MAX_DISPLAYED) {
            return;
        }

        $next = (int) $profile->approvedCredentials()->max('collaborator_credentials.position') + 1;
        $profile->approvedCredentials()->syncWithoutDetaching([$target->id => ['position' => $next]]);
    }

    private function guardUnsettled(VocabularyProposal $proposal): void
    {
        if ($proposal->isSettled()) {
            throw new InvalidArgumentException('That proposal has already been decided, and a decision is not re-made.');
        }
    }
}
