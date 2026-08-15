<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            ExpertiseAreaSeeder::class,
        ]);

        // Synthetic fixtures for local development and staging demos only.
        // Never real collaborator data (doc 01 §4).
        if (app()->environment(['local', 'testing'])) {
            $this->call(DemoDataSeeder::class);
        }
    }
}
