<?php

namespace App\Services\Expertise;

use App\Models\Discipline;
use App\Models\ExpertiseAlias;
use App\Models\ExpertiseArea;
use App\Models\User;
use App\Services\Governance\AuditRecorder;
use App\Support\VocabularyNormaliser;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * The Institute maintaining its own vocabulary, without a developer.
 *
 * Renaming keeps the record and changes the words, so every profile using the
 * term shows the new wording at once. That is the dividend of people selecting
 * entries rather than typing text. The slug is left alone on rename, because it
 * is the stable key the release seeder matches on; changing it would make the
 * next deployment think the term was new.
 *
 * Retiring never deletes. A retired term stops being offered to anybody new
 * and stays exactly where it is on the profiles that already hold it. History
 * is not rewritten by a change of policy.
 */
class TaxonomyAdmin
{
    public function __construct(private readonly AuditRecorder $audit) {}

    public function createField(User $actor, string $name, ?string $descriptor = null): Discipline
    {
        $name = $this->cleanName($name);
        $this->assertNameFree(Discipline::class, 'Field', $name);

        $field = Discipline::query()->create([
            'name' => $name,
            'slug' => $this->uniqueSlug(Discipline::class, $name),
            'descriptor' => filled($descriptor) ? trim((string) $descriptor) : null,
            'search_key' => VocabularyNormaliser::key($name),
            'active' => true,
            'sort_order' => (int) Discipline::query()->max('sort_order') + 1,
        ]);

        $this->audit->record('taxonomy.field.created', $actor, $field, ['name' => $name]);

        return $field;
    }

    public function updateField(User $actor, Discipline $field, string $name, ?string $descriptor, bool $active, ?int $sortOrder = null): Discipline
    {
        $name = $this->cleanName($name);
        $this->assertNameFree(Discipline::class, 'Field', $name, $field->id);
        $before = $field->only(['name', 'descriptor', 'active', 'sort_order']);

        $field->fill([
            'name' => $name,
            'descriptor' => filled($descriptor) ? trim((string) $descriptor) : null,
            'search_key' => VocabularyNormaliser::key($name),
            'active' => $active,
            'sort_order' => $sortOrder ?? $field->sort_order,
        ])->save();

        $this->audit->record('taxonomy.field.updated', $actor, $field, ['before' => $before, 'after' => $field->only(['name', 'descriptor', 'active', 'sort_order'])]);

        return $field;
    }

    /**
     * @param  array<int, int>  $fieldIds  The Fields this Specialty is recognizable within, for search.
     */
    public function createSpecialty(User $actor, string $name, array $fieldIds): ExpertiseArea
    {
        $name = $this->cleanName($name);
        $this->assertNameFree(ExpertiseArea::class, 'Specialty', $name);

        $specialty = ExpertiseArea::query()->create([
            'name' => $name,
            'slug' => $this->uniqueSlug(ExpertiseArea::class, $name),
            'search_key' => VocabularyNormaliser::key($name),
            'active' => true,
            'sort_order' => (int) ExpertiseArea::query()->max('sort_order') + 1,
        ]);

        $specialty->disciplines()->sync(array_map('intval', $fieldIds));

        $this->audit->record('taxonomy.specialty.created', $actor, $specialty, ['name' => $name, 'field_ids' => $fieldIds]);

        return $specialty;
    }

    /**
     * @param  array<int, int>  $fieldIds
     */
    public function updateSpecialty(User $actor, ExpertiseArea $specialty, string $name, bool $active, array $fieldIds): ExpertiseArea
    {
        $name = $this->cleanName($name);
        $this->assertNameFree(ExpertiseArea::class, 'Specialty', $name, $specialty->id);
        $before = $specialty->only(['name', 'active']);

        $specialty->fill([
            'name' => $name,
            'search_key' => VocabularyNormaliser::key($name),
            'active' => $active,
        ])->save();

        // Search relationships only. Changing them never touches what any
        // collaborator has claimed for themselves.
        $specialty->disciplines()->sync(array_map('intval', $fieldIds));

        $this->audit->record('taxonomy.specialty.updated', $actor, $specialty, ['before' => $before, 'after' => $specialty->only(['name', 'active']), 'field_ids' => $fieldIds]);

        return $specialty;
    }

    public function createAlias(User $actor, string $term, Discipline|ExpertiseArea $target, bool $ambiguous = false): ExpertiseAlias
    {
        $term = $this->cleanName($term);

        $alias = ExpertiseAlias::query()->create([
            'term' => $term,
            'search_key' => VocabularyNormaliser::key($term),
            'discipline_id' => $target instanceof Discipline ? $target->id : null,
            'expertise_area_id' => $target instanceof ExpertiseArea ? $target->id : null,
            'ambiguous' => $ambiguous,
            'active' => true,
        ]);

        $this->audit->record('taxonomy.alias.created', $actor, $alias, ['term' => $term, 'target' => $target->name]);

        return $alias;
    }

    public function retireAlias(User $actor, ExpertiseAlias $alias): void
    {
        $alias->forceFill(['active' => false])->save();

        $this->audit->record('taxonomy.alias.retired', $actor, $alias, ['term' => $alias->term]);
    }

    /**
     * Two entries with the same name in a controlled vocabulary would defeat
     * the vocabulary. Refuse in plain words rather than let the database
     * refuse in its own.
     *
     * @param  class-string<Discipline|ExpertiseArea>  $model
     */
    private function assertNameFree(string $model, string $kind, string $name, ?int $exceptId = null): void
    {
        $taken = $model::query()
            ->whereRaw('lower(name) = ?', [mb_strtolower($name)])
            ->when($exceptId !== null, fn ($q) => $q->whereKeyNot($exceptId))
            ->exists();

        if ($taken) {
            throw new InvalidArgumentException("A {$kind} called \"{$name}\" already exists.");
        }
    }

    private function cleanName(string $name): string
    {
        $name = trim(preg_replace('/\s+/', ' ', $name) ?? $name);

        if ($name === '') {
            throw new InvalidArgumentException('A term needs a name.');
        }

        return $name;
    }

    /**
     * @param  class-string<Discipline|ExpertiseArea>  $model
     */
    private function uniqueSlug(string $model, string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $n = 2;

        while ($model::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$n++;
        }

        return $slug;
    }
}
