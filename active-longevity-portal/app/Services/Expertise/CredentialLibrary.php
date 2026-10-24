<?php

namespace App\Services\Expertise;

use App\Models\Credential;
use App\Models\Profile;
use App\Models\User;
use App\Models\VocabularyProposal;
use App\Services\Governance\AuditRecorder;
use App\Support\VocabularyNormaliser;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Finding, choosing and requesting the credentials shown after a name.
 *
 * The search matches the meaning as well as the letters, so somebody who holds
 * a Master of Public Health but does not think of themselves as an MPH can type
 * "public health" and find it. People do not always know their own credential
 * as an acronym, and a search that matched only letters would send them to the
 * request form for something the library already holds.
 *
 * The same letters may return several entries. That is the design working, not
 * failing: the meaning and jurisdiction shown beside each one is how a person
 * picks the FRCP they actually hold.
 */
class CredentialLibrary
{
    public const MAX_DISPLAYED = 3;

    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * @return Collection<int, Credential>
     */
    public function search(string $term, int $limit = 12): Collection
    {
        $key = VocabularyNormaliser::key($term);

        if ($key === '') {
            return collect();
        }

        $acronymKey = VocabularyNormaliser::key($term);

        return Credential::query()
            ->selectable()
            ->where(fn ($q) => $q
                ->whereRaw('lower(acronym) = ?', [mb_strtolower(trim($term))])
                ->orWhere('search_key', 'like', $key.'%')
                ->orWhere('search_key', 'like', '%'.$key.'%'))
            ->get()
            ->sortBy(fn (Credential $c): string => sprintf(
                '%d|%s|%s',
                // An exact acronym first, then a meaning that starts with the
                // term, then anything containing it.
                mb_strtolower($c->acronym) === mb_strtolower(trim($term)) ? 0
                    : (str_starts_with((string) $c->search_key, $acronymKey) ? 1 : 2),
                $c->acronym,
                (string) $c->jurisdiction,
            ))
            ->take($limit)
            ->values();
    }

    /**
     * The credentials near enough to a proposed one that the person should see
     * them before submitting. Spares the Institute approving "Masters in Public
     * Health" when "Master of Public Health" already exists.
     *
     * @return Collection<int, Credential>
     */
    public function nearMatches(string $acronym, string $meaning): Collection
    {
        return $this->search($acronym, 4)
            ->merge($this->search($meaning, 4))
            ->unique('id')
            ->take(5)
            ->values();
    }

    /**
     * Replace the credentials a collaborator displays, in the order given.
     *
     * At most three, as agreed, and only from the approved library. The
     * database enforces the cap too, so no future screen can quietly permit a
     * fourth.
     *
     * @param  array<int, int>  $credentialIds
     */
    public function setDisplayed(Profile $profile, array $credentialIds): void
    {
        $ids = array_values(array_unique(array_map('intval', $credentialIds)));

        if (count($ids) > self::MAX_DISPLAYED) {
            throw new InvalidArgumentException('A profile may display at most three credentials after the name.');
        }

        $approved = Credential::query()->selectable()->whereIn('id', $ids)->pluck('id')->all();
        $ordered = array_values(array_filter($ids, static fn (int $id): bool => in_array($id, $approved, true)));

        $profile->approvedCredentials()->sync(
            collect($ordered)->mapWithKeys(fn (int $id, int $i): array => [$id => ['position' => $i]])->all()
        );

        $this->audit->record('profile.credentials.set', $profile->collaborator?->user, $profile, [
            'credential_ids' => $ordered,
        ]);
    }

    /**
     * Record a credential the library does not hold, and let the collaborator
     * carry on.
     *
     * It is attached to them and marked as awaiting approval, visible to them
     * and to the Institute, not displayed publicly and not offered to anybody
     * else. When the Institute approves it, it appears on their profile.
     */
    public function propose(Profile $profile, User $user, string $acronym, string $meaning, ?string $jurisdiction = null): VocabularyProposal
    {
        $acronym = trim($acronym);
        $meaning = trim($meaning);

        $existing = VocabularyProposal::query()
            ->where('profile_id', $profile->id)
            ->where('kind', VocabularyProposal::KIND_CREDENTIAL)
            ->whereRaw('lower(term) = ?', [mb_strtolower($acronym)])
            ->whereRaw('lower(coalesce(meaning, \'\')) = ?', [mb_strtolower($meaning)])
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        $proposal = VocabularyProposal::query()->create([
            'kind' => VocabularyProposal::KIND_CREDENTIAL,
            'term' => $acronym,
            'meaning' => $meaning,
            'jurisdiction' => filled($jurisdiction) ? trim((string) $jurisdiction) : null,
            'proposed_by_user_id' => $user->id,
            'profile_id' => $profile->id,
            'status' => VocabularyProposal::STATUS_PENDING,
        ]);

        $this->audit->record('vocabulary.proposed', $user, $proposal, [
            'kind' => VocabularyProposal::KIND_CREDENTIAL,
            'term' => $acronym,
        ]);

        return $proposal;
    }
}
