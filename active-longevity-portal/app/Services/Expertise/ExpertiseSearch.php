<?php

namespace App\Services\Expertise;

use App\Models\Discipline;
use App\Models\ExpertiseArea;
use App\Support\ExpertiseMatch;
use App\Support\VocabularyNormaliser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Finding the right Field or Specialty by typing, rather than by scrolling.
 *
 * Sixty seven Fields and four hundred Specialties are nothing to a search and
 * unbearable as a list, which is why selection is search-first everywhere,
 * including on a phone where a long list is worst of all.
 *
 * What it searches, in order of how strongly each result is trusted:
 *
 *  1. The controlled name itself, matched exactly.
 *  2. The controlled name, matched from the beginning.
 *  3. A true alias or recognized abbreviation, matched exactly.
 *  4. The controlled name, matched anywhere inside.
 *  5. An ambiguous abbreviation, which may mean several things.
 *
 * Ambiguous abbreviations come last deliberately. PT might be Physical Therapy
 * or a number of other things, so it offers every candidate rather than
 * confidently resolving to one. An alias that sends somebody confidently to the
 * wrong place is worse than no alias at all, because they do not know they have
 * been misdirected.
 *
 * Nothing here ever selects anything. It offers; the collaborator chooses.
 */
class ExpertiseSearch
{
    private const RANK_EXACT = 10;

    private const RANK_PREFIX = 20;

    private const RANK_ALIAS = 30;

    private const RANK_CONTAINS = 40;

    private const RANK_AMBIGUOUS = 60;

    /**
     * @return Collection<int, ExpertiseMatch>
     */
    public function search(string $term, int $limit = 20): Collection
    {
        $key = VocabularyNormaliser::key($term);

        if ($key === '') {
            return collect();
        }

        $matches = collect()
            ->merge($this->fields($key))
            ->merge($this->specialties($key))
            ->merge($this->viaAliases($key));

        // One entry may be found several ways at once. Keep the strongest
        // reason it appeared, so a Field found both by name and by alias is
        // shown as itself rather than as "also known as".
        return $matches
            // One sortable key rather than two sorts: an array passed to
            // sortBy is read as column specifications, not as a chain.
            ->sortBy(fn (ExpertiseMatch $m): string => sprintf('%03d|%s', $m->rank, mb_strtolower($m->name)))
            ->unique(fn (ExpertiseMatch $m): string => $m->kind.':'.$m->id)
            ->take($limit)
            ->values();
    }

    /**
     * Fields only. Used where a collaborator is choosing their professional
     * home rather than adding precision.
     *
     * @return Collection<int, ExpertiseMatch>
     */
    public function searchFields(string $term, int $limit = 20): Collection
    {
        return $this->search($term, 100)
            ->filter(fn (ExpertiseMatch $m): bool => $m->isField())
            ->take($limit)
            ->values();
    }

    /**
     * Specialties, optionally narrowed to one Field.
     *
     * Narrowing is a way of shortening the list, never a rule about what may be
     * chosen. A collaborator must still be able to search every Specialty
     * without choosing a Field first, because the person who does not know
     * which Field the Institute filed their work under is disproportionately
     * the interdisciplinary one, and those are the people the Institute most
     * wants to admit.
     *
     * @return Collection<int, ExpertiseMatch>
     */
    public function searchSpecialties(string $term, ?int $withinFieldId = null, int $limit = 20): Collection
    {
        $results = $this->search($term, 200)
            ->filter(fn (ExpertiseMatch $m): bool => ! $m->isField());

        if ($withinFieldId !== null) {
            $inField = DB::table('discipline_expertise_area')
                ->where('discipline_id', $withinFieldId)
                ->pluck('expertise_area_id')
                ->all();

            $results = $results->sortBy(
                fn (ExpertiseMatch $m): int => in_array($m->id, $inField, true) ? 0 : 1
            );
        }

        return $results->take($limit)->values();
    }

    /**
     * @return Collection<int, ExpertiseMatch>
     */
    private function fields(string $key): Collection
    {
        return Discipline::query()
            ->where('active', true)
            ->where(fn ($q) => $q
                ->where('search_key', $key)
                ->orWhere('search_key', 'like', $key.'%')
                ->orWhere('search_key', 'like', '%'.$key.'%'))
            ->limit(60)
            ->get()
            ->map(fn (Discipline $d): ExpertiseMatch => new ExpertiseMatch(
                kind: ExpertiseMatch::KIND_FIELD,
                id: $d->id,
                name: $d->name,
                descriptor: $d->descriptor,
                rank: $this->rankFor((string) $d->search_key, $key),
            ));
    }

    /**
     * @return Collection<int, ExpertiseMatch>
     */
    private function specialties(string $key): Collection
    {
        return ExpertiseArea::query()
            ->with('disciplines:id,name')
            ->where('active', true)
            ->where(fn ($q) => $q
                ->where('search_key', $key)
                ->orWhere('search_key', 'like', $key.'%')
                ->orWhere('search_key', 'like', '%'.$key.'%'))
            ->limit(80)
            ->get()
            ->map(fn (ExpertiseArea $s): ExpertiseMatch => new ExpertiseMatch(
                kind: ExpertiseMatch::KIND_SPECIALTY,
                id: $s->id,
                name: $s->name,
                matchedVia: null,
                rank: $this->rankFor((string) $s->search_key, $key),
                context: $s->disciplines->pluck('name')->implode(', '),
            ));
    }

    /**
     * Terms the collaborator might reasonably use that are not the Institute's
     * wording. The controlled term is what comes back.
     *
     * @return Collection<int, ExpertiseMatch>
     */
    private function viaAliases(string $key): Collection
    {
        $aliases = DB::table('expertise_aliases')
            ->where('active', true)
            ->where(fn ($q) => $q->where('search_key', $key)->orWhere('search_key', 'like', $key.'%'))
            ->limit(60)
            ->get();

        $fieldIds = $aliases->pluck('discipline_id')->filter()->unique()->all();
        $specialtyIds = $aliases->pluck('expertise_area_id')->filter()->unique()->all();

        $fields = Discipline::query()->whereIn('id', $fieldIds)->where('active', true)->get()->keyBy('id');
        $specialties = ExpertiseArea::query()->with('disciplines:id,name')
            ->whereIn('id', $specialtyIds)->where('active', true)->get()->keyBy('id');

        return $aliases->map(function (object $alias) use ($fields, $specialties): ?ExpertiseMatch {
            $rank = $alias->ambiguous ? self::RANK_AMBIGUOUS : self::RANK_ALIAS;

            if ($alias->discipline_id !== null) {
                $field = $fields->get($alias->discipline_id);

                return $field === null ? null : new ExpertiseMatch(
                    kind: ExpertiseMatch::KIND_FIELD,
                    id: $field->id,
                    name: $field->name,
                    descriptor: $field->descriptor,
                    matchedVia: $alias->term,
                    rank: $rank,
                );
            }

            $specialty = $specialties->get($alias->expertise_area_id);

            return $specialty === null ? null : new ExpertiseMatch(
                kind: ExpertiseMatch::KIND_SPECIALTY,
                id: $specialty->id,
                name: $specialty->name,
                matchedVia: $alias->term,
                rank: $rank,
                context: $specialty->disciplines->pluck('name')->implode(', '),
            );
        })->filter()->values();
    }

    private function rankFor(string $searchKey, string $typed): int
    {
        if ($searchKey === $typed) {
            return self::RANK_EXACT;
        }

        return str_starts_with($searchKey, $typed) ? self::RANK_PREFIX : self::RANK_CONTAINS;
    }
}
