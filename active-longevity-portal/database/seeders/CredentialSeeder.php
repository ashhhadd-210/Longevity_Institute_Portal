<?php

namespace Database\Seeders;

use App\Models\Credential;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * The starter credential library, for the Institute's review.
 *
 * A library only grows if it starts somewhere. Without a decent seed every
 * early collaborator meets an empty box and the Institute is flooded with
 * requests for PhD and MD. With one, most people find what they hold on the
 * first day and only the genuinely unusual reaches the review queue.
 *
 * Every entry is a record rather than an acronym. FRCP appears three times here
 * with three jurisdictions, and each is a different credential held by
 * different people. Matching is on the letters, the meaning and the
 * jurisdiction together, so re-running never duplicates and never collapses
 * two distinct fellowships into one.
 *
 * The contents come from a data file so that the Institute's corrections are a
 * change to data rather than to code.
 */
class CredentialSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/credentials.json');

        if (! is_file($path)) {
            throw new RuntimeException("The credential data file is missing: {$path}");
        }

        /** @var array{credentials: array<int, array{acronym: string, meaning: string, jurisdiction: string|null, type: string|null, issuing_body?: string|null, description?: string|null}>} $data */
        $data = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        foreach ($data['credentials'] as $row) {
            $credential = Credential::query()
                ->whereRaw('lower(acronym) = ?', [mb_strtolower($row['acronym'])])
                ->whereRaw('lower(meaning) = ?', [mb_strtolower($row['meaning'])])
                ->whereRaw("coalesce(lower(jurisdiction), '') = ?", [mb_strtolower((string) ($row['jurisdiction'] ?? ''))])
                ->first();

            // Create only. If the Institute has retired or corrected a credential
            // since, a release must leave that decision standing.
            if ($credential !== null) {
                continue;
            }

            $credential = new Credential;
            $credential->acronym = $row['acronym'];
            $credential->meaning = $row['meaning'];
            $credential->jurisdiction = $row['jurisdiction'];
            $credential->credential_type = $row['type'];
            $credential->issuing_body = $row['issuing_body'] ?? null;
            $credential->description = $row['description'] ?? null;
            $credential->approved = true;
            $credential->active = true;
            $credential->approved_at = now();
            $credential->save();
        }
    }
}
