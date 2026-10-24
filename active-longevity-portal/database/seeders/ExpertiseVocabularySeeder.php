<?php

namespace Database\Seeders;

use App\Models\Discipline;
use App\Models\ExpertiseArea;
use App\Support\VocabularyNormaliser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Installs the Institute's Field, Specialty and Alias Registers.
 *
 * The contents come from the Institute's own documents rather than from
 * anything invented here, and they are read from a data file so that the next
 * revision is a change to data rather than a change to code. The vocabulary is
 * the Institute's to grow; this seeder simply puts it in place.
 *
 * Everything matches on a stable key, so it is safe to run on every release and
 * safe to run twice. It creates and fills in only: a term the Institute has
 * added, renamed or retired for itself is never undone by running this again.
 * The current source is the v1.0 Registers of September 2026.
 */
class ExpertiseVocabularySeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/expertise-vocabulary.json');

        if (! is_file($path)) {
            throw new RuntimeException("The expertise vocabulary data file is missing: {$path}");
        }

        /** @var array{fields: array<int, string>, descriptors?: array<string, string>, specialties: array<int, array{name: string, fields: array<int, string>}>, retired_specialties?: array<int, string>, aliases: array<int, array{term: string, target: string, ambiguous: bool}>} $data */
        $data = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        DB::transaction(function () use ($data): void {
            $fields = $this->seedFields($data['fields']);
            $this->seedDescriptors($data['descriptors'] ?? [], $fields);
            $specialties = $this->seedSpecialties($data['specialties'], $fields);
            $this->retireSpecialties($data['retired_specialties'] ?? []);
            $this->seedAliases($data['aliases'], $fields, $specialties);
        });
    }

    /**
     * The distinguishing lines from the Field Register. A line is written only
     * where a Field has close neighbors, and it is filled in only where the
     * Field has none yet, so a line the Institute has already edited in the
     * Registers screen is left as they wrote it.
     *
     * @param  array<string, string>  $lines
     * @param  array<string, Discipline>  $fields
     */
    private function seedDescriptors(array $lines, array $fields): void
    {
        foreach ($lines as $name => $line) {
            $field = $fields[$name] ?? null;

            if ($field !== null && blank($field->descriptor)) {
                $field->forceFill(['descriptor' => $line])->save();
            }
        }
    }

    /**
     * A Specialty the Register has since withdrawn stops being offered but is
     * never deleted: anyone who already holds it keeps it, exactly as the
     * Institute's own retirement in the Registers screen behaves.
     *
     * @param  array<int, string>  $names
     */
    private function retireSpecialties(array $names): void
    {
        foreach ($names as $name) {
            ExpertiseArea::query()
                ->where('slug', Str::slug($name))
                ->where('active', true)
                ->update(['active' => false]);
        }
    }

    /**
     * @param  array<int, string>  $names
     * @return array<string, Discipline>
     */
    private function seedFields(array $names): array
    {
        $fields = [];

        foreach ($names as $position => $name) {
            // Create only. Once a Field exists, its name, order, descriptor and
            // whether it is offered belong to the Institute, who may change any
            // of them in the Registers screen. A release must never quietly
            // undo that, so an existing row is left exactly as found.
            $field = Discipline::query()->firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'search_key' => VocabularyNormaliser::key($name),
                    'active' => true,
                    'sort_order' => $position + 1,
                ],
            );

            $fields[$name] = $field;
        }

        return $fields;
    }

    /**
     * A Specialty exists once and relates to as many Fields as genuinely apply.
     * Those relationships serve search; they never assign anybody a Field.
     *
     * @param  array<int, array{name: string, fields: array<int, string>}>  $rows
     * @param  array<string, Discipline>  $fields
     * @return array<string, ExpertiseArea>
     */
    private function seedSpecialties(array $rows, array $fields): array
    {
        $specialties = [];

        foreach ($rows as $position => $row) {
            // Create only, for the same reason as Fields: the Institute's edits
            // in the Registers screen survive every release.
            $specialty = ExpertiseArea::query()->firstOrCreate(
                ['slug' => Str::slug($row['name'])],
                [
                    'name' => $row['name'],
                    'search_key' => VocabularyNormaliser::key($row['name']),
                    'active' => true,
                    'sort_order' => $position + 1,
                ],
            );

            $ids = [];

            foreach ($row['fields'] as $fieldName) {
                if (isset($fields[$fieldName])) {
                    $ids[] = $fields[$fieldName]->id;
                }
            }

            // syncWithoutDetaching, so a relationship the Institute has added
            // by hand is never silently removed by a later release.
            $specialty->disciplines()->syncWithoutDetaching($ids);

            $specialties[$row['name']] = $specialty;
        }

        return $specialties;
    }

    /**
     * An alias surfaces a controlled term for the collaborator to consider. It
     * resolves to the existing record and must never create a duplicate one,
     * so an alias whose target is not present is skipped rather than invented.
     *
     * @param  array<int, array{term: string, target: string, ambiguous: bool}>  $rows
     * @param  array<string, Discipline>  $fields
     * @param  array<string, ExpertiseArea>  $specialties
     */
    private function seedAliases(array $rows, array $fields, array $specialties): void
    {
        foreach ($rows as $row) {
            $target = trim($row['target']);

            $discipline = $fields[$target] ?? null;
            $specialty = $discipline ? null : ($specialties[$target] ?? null);

            if ($discipline === null && $specialty === null) {
                continue;
            }

            DB::table('expertise_aliases')->updateOrInsert(
                [
                    'search_key' => VocabularyNormaliser::key($row['term']),
                    'discipline_id' => $discipline?->id,
                    'expertise_area_id' => $specialty?->id,
                ],
                [
                    'term' => trim($row['term']),
                    'ambiguous' => $row['ambiguous'],
                    'active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }
    }
}
