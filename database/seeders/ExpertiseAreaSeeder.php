<?php

namespace Database\Seeders;

use App\Models\ExpertiseArea;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Placeholder vocabulary drawn from the disciplines named in the Institute's
 * founding documents. The Institute curates the real vocabulary before
 * Milestone 3; this seeder is idempotent and safe to re-run.
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
            ExpertiseArea::query()->updateOrCreate(['slug' => Str::slug($name)], [
                'name' => $name,
                'active' => true,
                'sort_order' => $index,
            ]);
        }
    }
}
