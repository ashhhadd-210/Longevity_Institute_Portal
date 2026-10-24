<?php

namespace App\Services\Profiles;

use App\Enums\ProfileEntrySection;
use App\Models\Profile;
use App\Models\ProfileLink;
use App\Models\VocabularyProposal;
use App\Services\Governance\AuditRecorder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Saving a collaborator's own account of their work.
 *
 * Profiles are completed over several sittings and often on different devices,
 * so every part saves on its own and nothing is lost by stopping halfway. There
 * is no wizard to get trapped in.
 *
 * A profile is ready for the directory once it carries what the Institute set
 * out in the reconciled architecture: a name, a Professional Identity, About
 * Your Work, at least one Field or a Field the person has proposed and the
 * Institute has yet to decide, a country, and a photograph. Optional sections
 * never count for or against it. Admitted is not the same as visible.
 */
class ProfileEditor
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * @param  array{professional_title?: string|null, one_line_summary?: string|null, about?: string|null, country_code?: string|null, city?: string|null, state_region?: string|null, collaboration_availability?: string|null, languages?: array<int, string>|null}  $data
     */
    public function saveDetails(Profile $profile, array $data): Profile
    {
        $profile->fill(Arr::only($data, [
            'professional_title', 'one_line_summary', 'about', 'country_code',
            'city', 'state_region', 'collaboration_availability', 'languages',
            'accepts_collaboration_interest',
        ]));

        $profile->save();
        $this->refreshCompletion($profile);

        $this->audit->record('profile.details.saved', $profile->collaborator?->user, $profile);

        return $profile;
    }

    /**
     * Replace the links on a profile.
     *
     * The collaborator decides the order and, by leaving a row blank, decides
     * that a slot should not exist at all. Nothing empty is stored, so nothing
     * empty can be published.
     *
     * @param  array<int, array{url?: string|null, label?: string|null}>  $rows
     */
    public function saveLinks(Profile $profile, array $rows): void
    {
        $clean = [];

        foreach ($rows as $row) {
            $url = trim((string) ($row['url'] ?? ''));
            $label = trim((string) ($row['label'] ?? ''));

            if ($url === '' || $label === '') {
                continue;
            }

            $clean[] = ['url' => $url, 'label' => $label];

            if (count($clean) === ProfileLink::MAX_PER_PROFILE) {
                break;
            }
        }

        DB::transaction(function () use ($profile, $clean): void {
            $profile->links()->delete();

            foreach ($clean as $position => $link) {
                $profile->links()->create([
                    'url' => $link['url'],
                    'label' => $link['label'],
                    'position' => $position + 1,
                ]);
            }
        });

        $this->audit->record('profile.links.saved', $profile->collaborator?->user, $profile, [
            'count' => count($clean),
        ]);
    }

    /**
     * Replace the positions a collaborator holds right now, up to three.
     *
     * The approved addition. Senior people frequently hold a chair, a hospital
     * role and an advisory seat at once, and each carries its own title,
     * organisation and department because none of the three describes the
     * other two. Blank rows are dropped rather than stored, and the database
     * refuses a fourth if any screen ever forgets to.
     *
     * @param  array<int, array{title?: string|null, organisation?: string|null, department?: string|null, country?: string|null, start_year?: int|string|null}>  $rows
     */
    public function savePositions(Profile $profile, array $rows): void
    {
        $clean = [];

        foreach ($rows as $row) {
            $title = trim((string) ($row['title'] ?? ''));
            $organisation = trim((string) ($row['organisation'] ?? ''));
            $department = trim((string) ($row['department'] ?? ''));
            $country = mb_strtoupper(trim((string) ($row['country'] ?? '')));
            $startYear = trim((string) ($row['start_year'] ?? ''));

            // A title is what makes a position. An organisation is optional,
            // because a legitimate role does not always sit inside one.
            if ($title === '') {
                continue;
            }

            $clean[] = [
                'title' => $title,
                'organisation' => $organisation !== '' ? $organisation : null,
                'department' => $department !== '' ? $department : null,
                'country' => $country !== '' ? $country : null,
                'start_year' => $startYear !== '' ? (int) $startYear : null,
            ];

            if (count($clean) === ProfileEntrySection::CURRENT_POSITION_LIMIT) {
                break;
            }
        }

        DB::transaction(function () use ($profile, $clean): void {
            $profile->currentPositions()->delete();

            foreach ($clean as $index => $payload) {
                $profile->entries()->create([
                    'section' => ProfileEntrySection::CurrentPosition,
                    'position' => $index + 1,
                    'payload' => $payload,
                ]);
            }
        });

        $this->audit->record('profile.positions.saved', $profile->collaborator?->user, $profile, [
            'count' => count($clean),
        ]);
    }

    /**
     * Whether this profile says enough for somebody else to recognize the
     * person, which is what the directory requires.
     *
     * Deliberately modest. The bar is recognition, not completeness: a profile
     * with three things filled in thoughtfully is worth more than one with
     * everything filled in carelessly, and a high bar would simply keep good
     * people out of the directory.
     */
    public function isComplete(Profile $profile): bool
    {
        $user = $profile->collaborator?->user;

        return $user !== null
            && trim((string) $user->first_name) !== ''
            && trim((string) $user->last_name) !== ''
            && trim((string) $profile->professional_title) !== ''
            && trim((string) $profile->one_line_summary) !== ''
            && ($profile->fields()->exists() || $this->hasPendingFieldProposal($profile))
            && trim((string) $profile->country_code) !== ''
            && filled($profile->photo_path);
    }

    /**
     * What is still needed before the profile is ready, in the order the
     * profile page presents it, each with the section it lives in. Empty when
     * the profile is ready. Optional sections never appear here.
     *
     * @return array<int, array{label: string, anchor: string}>
     */
    public function missingForReadiness(Profile $profile): array
    {
        $user = $profile->collaborator?->user;
        $missing = [];

        if ($user === null || trim((string) $user->first_name) === '' || trim((string) $user->last_name) === '') {
            $missing[] = ['label' => 'Your name', 'anchor' => 'name'];
        }
        if (blank($profile->photo_path)) {
            $missing[] = ['label' => 'A photograph', 'anchor' => 'photograph'];
        }
        if (trim((string) $profile->professional_title) === '') {
            $missing[] = ['label' => 'Professional Identity', 'anchor' => 'identity'];
        }
        if (trim((string) $profile->one_line_summary) === '') {
            $missing[] = ['label' => 'About Your Work', 'anchor' => 'about-your-work'];
        }
        if (trim((string) $profile->country_code) === '') {
            $missing[] = ['label' => 'Country', 'anchor' => 'country'];
        }
        if (! $profile->fields()->exists() && ! $this->hasPendingFieldProposal($profile)) {
            $missing[] = ['label' => 'At least one Field', 'anchor' => 'fields'];
        }

        return $missing;
    }

    /**
     * A missing Field must never be a dead end, so a Field the person has
     * proposed and the Institute has yet to decide counts as having one.
     */
    private function hasPendingFieldProposal(Profile $profile): bool
    {
        return $profile->vocabularyProposals()
            ->where('kind', VocabularyProposal::KIND_FIELD)
            ->where('status', VocabularyProposal::STATUS_PENDING)
            ->exists();
    }

    /**
     * Completion is recorded rather than recalculated on every page, and the
     * moment it happened is kept, because the Institute may want to know when
     * somebody finished rather than merely that they did.
     */
    public function refreshCompletion(Profile $profile): void
    {
        $complete = $this->isComplete($profile);

        if ($complete && $profile->completed_at === null) {
            $profile->forceFill(['completed_at' => now()])->save();
            $this->audit->record('profile.completed', $profile->collaborator?->user, $profile);

            return;
        }

        if (! $complete && $profile->completed_at !== null) {
            $profile->forceFill(['completed_at' => null])->save();
            $this->audit->record('profile.incomplete', $profile->collaborator?->user, $profile);
        }
    }
}
