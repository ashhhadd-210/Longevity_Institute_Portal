<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Institute-controlled vocabularies. These are real reference data, not
        // fixtures: production needs them, and each is safe to re-run because
        // every row is matched on its stable key.
        $this->call([
            RolePermissionSeeder::class,
            ExpertiseAreaSeeder::class,
            ReferralRelationshipSeeder::class,
        ]);

        // Synthetic fixtures for local development and staging demonstrations
        // only. Staging never holds real collaborator data (doc 01 §4), and
        // production is never seeded with fixtures at all.
        if (app()->environment(['local', 'testing', 'staging'])) {
            $this->call(DemoDataSeeder::class);
        }
    }
}
