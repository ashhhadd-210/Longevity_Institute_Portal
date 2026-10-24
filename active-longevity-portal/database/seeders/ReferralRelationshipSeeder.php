<?php

namespace Database\Seeders;

use App\Models\ReferralRelationship;
use Illuminate\Database\Seeder;

/**
 * The referrer relationship vocabulary as agreed with the Institute on
 * 27 August 2026.
 *
 * Her list, with one adjustment she accepted: "Mentor" and "Mentee" are stated
 * directionally. Asked "What is your relationship to this person?", a bare
 * "Mentor" cannot say who mentored whom, and the two make for very different
 * introductions. Every other option is symmetric — a colleague's colleague is a
 * colleague — and stands exactly as she wrote it.
 *
 * "Prefer not to specify" is a deliberate option rather than an omission. The
 * question is required precisely so that choosing it means something: the
 * referrer considered the question and chose discretion, which a blank field
 * could never tell us.
 *
 * The list is seeded, not hardcoded. The Institute refines it as real referrals
 * show what people actually want to say.
 */
class ReferralRelationshipSeeder extends Seeder
{
    public function run(): void
    {
        $relationships = [
            ['key' => 'colleague', 'label' => 'Colleague'],
            ['key' => 'former_colleague', 'label' => 'Former colleague'],
            ['key' => 'research_collaborator', 'label' => 'Research collaborator'],
            ['key' => 'professional_acquaintance', 'label' => 'Professional acquaintance'],
            ['key' => 'friend', 'label' => 'Friend'],
            ['key' => 'family_member', 'label' => 'Family member'],
            ['key' => 'mentored_them', 'label' => 'I mentored them'],
            ['key' => 'mentored_by_them', 'label' => 'They mentored me'],
            ['key' => 'other', 'label' => 'Other', 'requires_detail' => true],
            ['key' => 'prefer_not_to_specify', 'label' => 'Prefer not to specify'],
        ];

        foreach ($relationships as $index => $relationship) {
            ReferralRelationship::query()->updateOrCreate(
                ['key' => $relationship['key']],
                [
                    'label' => $relationship['label'],
                    'requires_detail' => $relationship['requires_detail'] ?? false,
                    'active' => true,
                    'sort_order' => ($index + 1) * 10,
                ],
            );
        }
    }
}
