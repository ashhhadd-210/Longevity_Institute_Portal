<?php

namespace App\Http\Controllers;

use App\Enums\CollaboratorStatus;
use App\Models\Discipline;
use App\Models\Profile;
use App\Support\VocabularyNormaliser;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * The community directory.
 *
 * Admitted is not the same as visible. A profile appears once it is complete,
 * which is what stops the directory filling with entries that say nothing, and
 * the Institute holds a recorded override for the cases that deserve one.
 *
 * Only Fields that actually contain somebody are offered for browsing. With
 * eight Alpha participants across sixty seven Fields, a list showing fifty nine
 * empty categories would make a growing Institute look deserted. Nothing is
 * hidden by this: the Register is untouched, and a Field appears the moment
 * somebody occupies it.
 *
 * Counts are deliberately not shown yet. At Alpha size, "Medicine 1" reads as a
 * disappointment rather than as information. They become useful once they
 * flatter, which is a switch the Institute turns whenever it judges the moment.
 */
class DirectoryController extends Controller
{
    private const SHOW_COUNTS = false;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'field' => ['nullable', 'integer', 'exists:disciplines,id'],
            'country' => ['nullable', 'string', 'size:2'],
        ]);

        return view('collaborator.directory', [
            'profiles' => $this->results($filters),
            'fields' => $this->occupiedFields(),
            'countries' => $this->occupiedCountries(),
            'filters' => $filters,
            'showCounts' => self::SHOW_COUNTS,
        ]);
    }

    public function show(Profile $profile): View
    {
        abort_unless($profile->isListable(), 404);

        $profile->load([
            'collaborator.user', 'fields', 'specialties.disciplines', 'links', 'organisation',
            'currentPositions', 'approvedCredentials',
        ]);

        return view('collaborator.profile-show', ['profile' => $profile]);
    }

    /**
     * @param  array{q?: string|null, field?: int|null, country?: string|null}  $filters
     * @return LengthAwarePaginator<int, Profile>
     */
    private function results(array $filters): LengthAwarePaginator
    {
        $query = Profile::query()
            ->whereNotNull('completed_at')
            ->whereHas('collaborator', fn ($q) => $q->where('status', CollaboratorStatus::Active->value)->where('directory_visible', true))
            ->with(['collaborator.user', 'approvedCredentials']);

        if (filled($filters['q'] ?? null)) {
            $term = trim((string) $filters['q']);
            $key = VocabularyNormaliser::key($term);

            $query->where(function ($q) use ($term, $key): void {
                // By name, as a person would type it.
                $q->whereHas('collaborator.user', function ($u) use ($term): void {
                    $u->where('first_name', 'ilike', '%'.$term.'%')
                        ->orWhere('last_name', 'ilike', '%'.$term.'%')
                        ->orWhere('preferred_name', 'ilike', '%'.$term.'%');
                })
                    ->orWhere('professional_title', 'ilike', '%'.$term.'%')
                    // Or by expertise, at either level, and a Specialty finds
                    // its person without that person claiming its Fields.
                    ->orWhereHas('fields', fn ($f) => $f->where('search_key', 'like', '%'.$key.'%'))
                    ->orWhereHas('specialties', fn ($s) => $s->where('search_key', 'like', '%'.$key.'%'));
            });
        }

        if (filled($filters['field'] ?? null)) {
            $fieldId = (int) $filters['field'];

            // Searching a Field finds everybody in it, including those reached
            // through a Specialty recognizable within it.
            $query->where(function ($q) use ($fieldId): void {
                $q->whereHas('fields', fn ($f) => $f->where('disciplines.id', $fieldId))
                    ->orWhereHas('specialties.disciplines', fn ($d) => $d->where('disciplines.id', $fieldId));
            });
        }

        if (filled($filters['country'] ?? null)) {
            $query->where('country_code', mb_strtoupper((string) $filters['country']));
        }

        return $query
            ->join('collaborators', 'collaborators.id', '=', 'profiles.collaborator_id')
            ->join('users', 'users.id', '=', 'collaborators.user_id')
            ->orderBy('users.last_name')
            ->orderBy('users.first_name')
            ->select('profiles.*')
            ->paginate(24)
            ->withQueryString();
    }

    /**
     * Fields that actually contain somebody, whether claimed directly or
     * reached through a Specialty.
     *
     * @return Collection<int, Discipline>
     */
    private function occupiedFields(): Collection
    {
        return Discipline::query()
            ->where('active', true)
            ->where(function ($q): void {
                $q->whereHas('profiles', fn ($p) => $p->whereNotNull('completed_at'))
                    ->orWhereHas('specialties.profiles', fn ($p) => $p->whereNotNull('completed_at'));
            })
            ->orderBy('name')
            ->get();
    }

    /** @return array<int, string> */
    private function occupiedCountries(): array
    {
        return Profile::query()
            ->whereNotNull('completed_at')
            ->whereNotNull('country_code')
            ->distinct()
            ->orderBy('country_code')
            ->pluck('country_code')
            ->all();
    }
}
