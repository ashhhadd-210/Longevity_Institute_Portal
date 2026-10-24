<?php

namespace App\Console\Commands;

use Database\Seeders\CredentialSeeder;
use Database\Seeders\ExpertiseAreaSeeder;
use Database\Seeders\ExpertiseVocabularySeeder;
use Database\Seeders\ReferralRelationshipSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Console\Command;

/**
 * Installs the Institute's controlled vocabularies into whichever environment
 * this is run in.
 *
 * These are reference data, not fixtures. Roles and permissions, the expertise
 * vocabulary and the referrer relationship list are part of how the Portal
 * works, and an environment without them is broken rather than merely empty:
 * the referral form, for instance, requires a relationship to be chosen and
 * would offer nothing to choose from.
 *
 * Every seeder it runs matches rows on a stable key and updates in place, so
 * this is safe to run on every deployment and safe to run twice. It never
 * touches collaborator data, and it never removes a row the Institute has
 * added for itself, because the vocabularies are theirs to grow.
 */
class SyncReferenceData extends Command
{
    protected $signature = 'portal:sync-reference-data';

    protected $description = "Install or update the Institute's controlled vocabularies (safe to re-run)";

    public function handle(): int
    {
        foreach ([
            'Roles and permissions' => RolePermissionSeeder::class,
            'Expertise areas (Milestone 1)' => ExpertiseAreaSeeder::class,
            'Referrer relationships' => ReferralRelationshipSeeder::class,
            'Fields, Specialties and aliases' => ExpertiseVocabularySeeder::class,
            'Credential library' => CredentialSeeder::class,
        ] as $label => $seeder) {
            $this->components->task($label, function () use ($seeder): void {
                $this->callSilent('db:seed', ['--class' => $seeder, '--force' => true]);
            });
        }

        $this->newLine();
        $this->components->info('Reference data is up to date.');

        return self::SUCCESS;
    }
}
