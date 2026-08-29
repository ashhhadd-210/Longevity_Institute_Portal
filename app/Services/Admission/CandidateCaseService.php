<?php

namespace App\Services\Admission;

use App\Enums\MatchConfidence;
use App\Models\Application;
use App\Models\CandidateCase;
use App\Models\Invitation;
use App\Models\Referral;
use App\Models\User;
use App\Services\Governance\AuditRecorder;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * One person, one case, one decision, one invitation, one admission history
 * (agreed with the Institute, 18 August 2026).
 *
 * Somebody may apply unprompted while a collaborator independently recommends
 * them. Both submissions are kept, because each carries something the other
 * lacks, and both are attached to a single case so that the Institute judges
 * the person once rather than twice.
 *
 * Nothing in this class merges anything on its own. Matches are recomputed and
 * offered as suggestions; joining two cases is always a human act, recorded
 * with the name of the person who performed it. An email address is strong
 * evidence and a name is weak evidence, but neither is proof, and a wrongly
 * merged pair of people is far harder to repair than an unmerged one.
 */
class CandidateCaseService
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * Open a case for a submission that has just arrived. Always a fresh case:
     * convergence happens by confirmation afterwards, never by guess at intake.
     */
    public function openFor(Application|Referral $submission): CandidateCase
    {
        return DB::transaction(function () use ($submission): CandidateCase {
            $case = CandidateCase::query()->create([
                'email' => $submission instanceof Application
                    ? $submission->email
                    : $submission->candidate_email,
                'display_name' => $submission instanceof Application
                    ? $submission->fullName()
                    : $submission->candidateFullName(),
            ]);

            $submission->candidate_case_id = $case->id;
            $submission->save();

            return $case;
        });
    }

    /**
     * Cases that may describe the same person as this one, strongest first.
     *
     * Recomputed on demand rather than stored, so that a suggestion reflects
     * the records as they are now. Only unmerged, undecided cases are offered:
     * proposing that she join a case she has already judged would invite her to
     * undo a decision by accident.
     *
     * @return Collection<int, array{case: CandidateCase, confidence: MatchConfidence}>
     */
    public function possibleDuplicatesFor(CandidateCase $case): Collection
    {
        $email = $case->email;
        $name = $case->display_name;

        /** @var Collection<int, CandidateCase> $candidates */
        $candidates = CandidateCase::query()
            ->where('id', '!=', $case->id)
            ->where(function ($query) use ($email, $name): void {
                if (filled($email)) {
                    $query->orWhere('email', $email);
                }

                $query->orWhereRaw('lower(display_name) = lower(?)', [$name]);
            })
            ->with(['applications', 'referrals'])
            ->get();

        return $candidates
            ->map(fn (CandidateCase $other): array => [
                'case' => $other,
                'confidence' => (filled($email) && $other->email !== null && strcasecmp($other->email, $email) === 0)
                    ? MatchConfidence::Confident
                    : MatchConfidence::Possible,
            ])
            ->sortBy(fn (array $match): int => $match['confidence'] === MatchConfidence::Confident ? 0 : 1)
            ->values();
    }

    /**
     * Join two cases, on a reviewer's judgement and never otherwise.
     *
     * Every submission moves onto the surviving case, so the Institute keeps
     * both accounts of the person while judging them once. The absorbed case is
     * left behind deliberately: its identifier may already appear in the
     * institutional record, and deleting it would put a hole in a history that
     * is supposed to be permanent.
     */
    public function join(CandidateCase $absorbed, CandidateCase $surviving, User $actor): CandidateCase
    {
        if ($absorbed->is($surviving)) {
            throw new DomainException('A case cannot be joined to itself.');
        }

        if ($absorbed->decisions()->exists() || $surviving->decisions()->exists()) {
            throw new DomainException(
                'One of these cases has already been decided. Joining them now would fold a judgement into a case it was not made about.'
            );
        }

        return DB::transaction(function () use ($absorbed, $surviving, $actor): CandidateCase {
            $absorbed->applications()->update(['candidate_case_id' => $surviving->id]);
            $absorbed->referrals()->update(['candidate_case_id' => $surviving->id]);

            $surviving->update([
                'linked_by' => $actor->id,
                'linked_at' => now(),
                'email' => $surviving->email ?? $absorbed->email,
            ]);

            $this->audit->record('admission.case.joined', $actor, $surviving, [
                'absorbed_case_id' => $absorbed->id,
                'absorbed_display_name' => $absorbed->display_name,
            ]);

            return $surviving->fresh();
        });
    }

    /**
     * The invitation, if any, that stands in the way of issuing another.
     *
     * A referral for somebody already invited or admitted must show the
     * existing case rather than quietly starting a second track for the same
     * person. The database enforces this too; this is the version that can
     * explain itself to a human being.
     */
    public function invitationBlocking(CandidateCase $case): ?Invitation
    {
        return $case->liveInvitation();
    }

    /**
     * Cases that already exist for an incoming email address, used at intake to
     * tell a referrer honestly that the Institute already knows this person,
     * without revealing anything about what was decided.
     *
     * @return Collection<int, CandidateCase>
     */
    public function existingCasesForEmail(?string $email): Collection
    {
        if (blank($email)) {
            return collect();
        }

        return CandidateCase::query()->where('email', $email)->get();
    }
}
