<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discipline;
use App\Models\ExpertiseAlias;
use App\Models\ExpertiseArea;
use App\Models\User;
use App\Services\Expertise\TaxonomyAdmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

/**
 * The Institute maintaining Fields, Specialties and aliases itself.
 *
 * Routine vocabulary management without a developer, as agreed: add, rename,
 * reorder and retire. Renaming changes every profile at once; retiring stops
 * offering a term to anybody new and leaves it where it is for those who
 * already hold it.
 */
class TaxonomyController extends Controller
{
    public function __construct(private readonly TaxonomyAdmin $taxonomy) {}

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $fields = Discipline::query()
            ->withCount(['specialties', 'profiles'])
            ->when($q !== '', fn ($query) => $query->where('name', 'ilike', '%'.$q.'%'))
            ->orderBy('sort_order')->orderBy('name')
            ->get();

        $specialties = ExpertiseArea::query()
            ->with('disciplines:id,name')
            ->withCount('profiles')
            ->when($q !== '', fn ($query) => $query->where('name', 'ilike', '%'.$q.'%'))
            ->orderBy('name')
            ->get();

        $aliases = ExpertiseAlias::query()
            ->with(['discipline:id,name', 'expertiseArea:id,name'])
            ->where('active', true)
            ->when($q !== '', fn ($query) => $query->where('term', 'ilike', '%'.$q.'%'))
            ->orderBy('term')
            ->get();

        return view('admin.taxonomy.index', [
            'q' => $q,
            'fields' => $fields,
            'specialties' => $specialties,
            'aliases' => $aliases,
            'allFields' => Discipline::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function storeField(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'descriptor' => ['nullable', 'string', 'max:300'],
        ]);

        return $this->attempt(fn () => $this->taxonomy->createField($this->actor($request), $v['name'], $v['descriptor'] ?? null), 'Field added.');
    }

    public function updateField(Request $request, Discipline $field): RedirectResponse
    {
        $v = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'descriptor' => ['nullable', 'string', 'max:300'],
            'active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        return $this->attempt(fn () => $this->taxonomy->updateField(
            $this->actor($request), $field, $v['name'], $v['descriptor'] ?? null,
            (bool) ($v['active'] ?? false), isset($v['sort_order']) ? (int) $v['sort_order'] : null,
        ), 'Field saved. Every profile using it now shows the new wording.');
    }

    public function storeSpecialty(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'field_ids' => ['array'],
            'field_ids.*' => ['integer', 'exists:disciplines,id'],
        ]);

        return $this->attempt(fn () => $this->taxonomy->createSpecialty($this->actor($request), $v['name'], array_map('intval', $v['field_ids'] ?? [])), 'Specialty added.');
    }

    public function updateSpecialty(Request $request, ExpertiseArea $specialty): RedirectResponse
    {
        $v = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'active' => ['nullable', 'boolean'],
            'field_ids' => ['array'],
            'field_ids.*' => ['integer', 'exists:disciplines,id'],
        ]);

        return $this->attempt(fn () => $this->taxonomy->updateSpecialty(
            $this->actor($request), $specialty, $v['name'], (bool) ($v['active'] ?? false), array_map('intval', $v['field_ids'] ?? []),
        ), 'Specialty saved.');
    }

    public function storeAlias(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'term' => ['required', 'string', 'max:100'],
            'target' => ['required', 'string', 'regex:/^(field|specialty):\d+$/'],
            'ambiguous' => ['nullable', 'boolean'],
        ]);

        [$kind, $id] = explode(':', $v['target']);
        $target = $kind === 'field'
            ? Discipline::query()->find((int) $id)
            : ExpertiseArea::query()->find((int) $id);

        if ($target === null) {
            return redirect()->route('admin.taxonomy.index')->withErrors(['target' => 'That term no longer exists.']);
        }

        return $this->attempt(fn () => $this->taxonomy->createAlias($this->actor($request), $v['term'], $target, (bool) ($v['ambiguous'] ?? false)), 'Alias added.');
    }

    public function retireAlias(Request $request, ExpertiseAlias $alias): RedirectResponse
    {
        $this->taxonomy->retireAlias($this->actor($request), $alias);

        return redirect()->route('admin.taxonomy.index')->with('status', 'Alias retired.');
    }

    private function actor(Request $request): User
    {
        $user = $request->user();
        abort_if($user === null, 403);

        return $user;
    }

    private function attempt(callable $action, string $success): RedirectResponse
    {
        try {
            $action();
        } catch (InvalidArgumentException $e) {
            return redirect()->route('admin.taxonomy.index')->withErrors(['taxonomy' => $e->getMessage()]);
        }

        return redirect()->route('admin.taxonomy.index')->with('status', $success);
    }
}
