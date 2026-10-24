<?php

namespace Database\Seeders;

use App\Models\ExpertiseArea;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Placeholder vocabulary drawn from the disciplines named in the Institute's
 * founding documents, superseded by the Registers loaded in Milestone 3 but
 * kept so that nothing referencing these rows breaks.
 *
 * Create only. Once a row exists its wording belongs to the Institute, who may
 * rename or retire it in the Registers screen, and a release must never put the
 * old wording back.
 */
class ExpertiseAreaSeeder extends Seeder
{
    /** @var list<string> */
    private array $areas = [
        'Medicine',
        'Public Health',
        'Exercise Science',
        'Nutrition Science',
        'Psychology',
        'Neuroscience',
        'Gerontology',
        'Sociology',
        'Health Policy',
        'Technology & Digital Health',
        'Economics',
        'Education',
        'Architecture & Urban Design',
    ];

    public function run(): void
    {
        foreach ($this->areas as $index => $name) {
            ExpertiseArea::query()->firstOrCreate(['slug' => Str::slug($name)], [
                'name' => $name,
                'active' => true,
                'sort_order' => $index,
            ]);
        }
    }
}
