<?php

namespace App\Http\Controllers;

use App\Enums\ProfileEntrySection;
use App\Models\Profile;
use App\Models\ProfileLink;
use App\Models\VocabularyProposal;
use App\Services\Profiles\ProfileEditor;
use App\Support\Countries;
use App\Support\FieldLimits;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * A collaborator's own account of their work.
 *
 * Each section saves on its own, because people fill these in over several
 * sittings and often on different devices. Nothing is lost by stopping halfway
 * and nobody is trapped in a wizard.
 */
class ProfileController extends Controller
{
    public function __construct(private readonly ProfileEditor $editor) {}

    public function edit(Request $request): View
    {
        $profile = $this->profileFor($request);
        $user = $request->user();
        $standardsVersion = (string) config('portal.profile_standards_version');

        // The Standards are shown on first entry, and again only when their
        // wording changes. Showing them is what is recorded: there is no box
        // to tick, as the Institute asked.
        $showStandards = $user !== null && $user->profile_standards_version !== $standardsVersion;

        if ($showStandards) {
            $user->forceFill([
                'profile_standards_shown_at' => now(),
                'profile_standards_version' => $standardsVersion,
            ])->save();
        }

        return view('collaborator.profile-edit', [
            'profile' => $profile,
            'links' => $profile->links()->orderBy('position')->get(),
            'fields' => $profile->fields()->get(),
            'specialties' => $profile->specialties()->get(),
            'credentials' => $profile->approvedCredentials()->get(),
            'positions' => $profile->currentPositions()->get(),
            'maxPositions' => ProfileEntrySection::CURRENT_POSITION_LIMIT,
            'credentialProposals' => $profile->vocabularyProposals()
                ->where('kind', VocabularyProposal::KIND_CREDENTIAL)
                ->latest()
                ->get(),
            'complete' => $this->editor->isComplete($profile),
            'missing' => $this->editor->missingForReadiness($profile),
            'user' => $user,
            'countries' => Countries::all(),
            'listed' => $profile->fresh()?->isListable() ?? false,
            'maxLinks' => ProfileLink::MAX_PER_PROFILE,
            'showStandards' => $showStandards,
        ]);
    }

    /** The Standards remain readable at any time after first entry. */
    public function standards(): View
    {
        return view('collaborator.profile-standards');
    }

    public function updateDetails(Request $request): RedirectResponse
    {
        if (is_string($request->input('country_code'))) {
            $request->merge(['country_code' => mb_strtoupper(trim($request->input('country_code')))]);
        }

        $validated = $request->validate([
            'professional_title' => ['nullable', 'string', 'max:'.FieldLimits::TITLE],
            'one_line_summary' => ['nullable', 'string', 'max:'.FieldLimits::ABOUT_YOUR_WORK],
            'about' => ['nullable', 'string', 'max:'.FieldLimits::ABOUT],
            'country_code' => ['nullable', 'string', 'size:2', Rule::in(array_keys(Countries::all()))],
            'city' => ['nullable', 'string', 'max:200'],
            'state_region' => ['nullable', 'string', 'max:200'],
            'collaboration_availability' => ['nullable', 'string', 'max:255'],
            'accepts_collaboration_interest' => ['nullable', 'boolean'],
        ], [], [
            'professional_title' => 'Professional Identity',
            'one_line_summary' => 'About Your Work',
            'about' => 'Additional Information',
        ]);

        if (isset($validated['country_code'])) {
            $validated['country_code'] = mb_strtoupper($validated['country_code']);
        }

        // An unticked checkbox sends nothing at all, which must mean "no" rather
        // than "leave it as it was".
        $validated['accepts_collaboration_interest'] = (bool) ($validated['accepts_collaboration_interest'] ?? false);

        $this->editor->saveDetails($this->profileFor($request), $validated);

        return redirect()
            ->route('profile.edit')
            ->with('status', 'Your profile has been saved.');
    }

    /**
     * The approved addition: up to three positions held at once, each with its
     * own title, organisation and department.
     */
    public function updatePositions(Request $request): RedirectResponse
    {
        // Country codes are matched in capitals, however they arrive.
        $request->merge(['positions' => array_map(
            fn ($row) => is_array($row) && isset($row['country']) && is_string($row['country'])
                ? [...$row, 'country' => mb_strtoupper(trim($row['country']))]
                : $row,
            (array) $request->input('positions', []),
        )]);

        $validated = $request->validate([
            'positions' => ['array', 'max:'.ProfileEntrySection::CURRENT_POSITION_LIMIT],
            'positions.*.title' => ['nullable', 'string', 'max:'.FieldLimits::TITLE],
            'positions.*.organisation' => ['nullable', 'string', 'max:'.FieldLimits::ORGANISATION],
            'positions.*.department' => ['nullable', 'string', 'max:'.FieldLimits::DEPARTMENT],
            'positions.*.country' => ['nullable', 'string', 'size:2', Rule::in(array_keys(Countries::all()))],
            'positions.*.start_year' => ['nullable', 'integer', 'min:1940', 'max:'.(now()->year + 1)],
        ], [
            'positions.*.start_year.integer' => 'A start year is four digits, for example 2019.',
            'positions.max' => 'A profile may show up to three current positions.',
        ], [
            'positions.*.title' => 'title',
            'positions.*.organisation' => 'organization',
            'positions.*.department' => 'department or division',
            'positions.*.country' => 'country',
            'positions.*.start_year' => 'start year',
        ]);

        /** @var array<int, array{title?: string|null, organisation?: string|null, department?: string|null, country?: string|null, start_year?: int|string|null}> $rows */
        $rows = $validated['positions'] ?? [];

        $this->editor->savePositions($this->profileFor($request), $rows);

        return redirect()
            ->route('profile.edit')
            ->with('status', 'Your positions have been saved.');
    }

    /**
     * The person's name as they use it professionally, and the name they go
     * by. Names are entered at registration and can be corrected here at any
     * time, because a profile is theirs to keep accurate.
     */
    public function updateName(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:'.FieldLimits::NAME],
            'middle_name' => ['nullable', 'string', 'max:'.FieldLimits::NAME],
            'last_name' => ['required', 'string', 'max:'.FieldLimits::NAME],
            'name_suffix' => ['nullable', 'string', 'max:'.FieldLimits::NAME_SUFFIX],
            'preferred_name' => ['nullable', 'string', 'max:'.FieldLimits::NAME],
        ], [], [
            'first_name' => 'first name or initial',
            'last_name' => 'family name',
            'preferred_name' => 'name you go by',
        ]);

        $user = $request->user();
        abort_if($user === null, 403);

        $user->forceFill(array_map(
            fn ($value) => is_string($value) && trim($value) !== '' ? trim($value) : null,
            $validated,
        ))->save();

        $this->editor->refreshCompletion($this->profileFor($request));

        return redirect()->route('profile.edit')->with('status', 'Your name has been saved.');
    }

    public function updateLinks(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'links' => ['array', 'max:'.ProfileLink::MAX_PER_PROFILE],
            'links.*.label' => ['nullable', 'string', 'max:'.FieldLimits::LINK_LABEL],
            // Only http and https, as agreed. Anything else is not a link the
            // Institute is willing to send a collaborator to.
            'links.*.url' => ['nullable', 'string', 'max:2048', 'url:http,https'],
        ], [
            'links.*.url.url' => 'Each link must be a web address beginning http or https.',
            'links.*.label.max' => 'A link label may be up to :max characters.',
        ]);

        /** @var array<int, array{url?: string|null, label?: string|null}> $rows */
        $rows = $validated['links'] ?? [];

        $this->editor->saveLinks($this->profileFor($request), $rows);

        return redirect()
            ->route('profile.edit')
            ->with('status', 'Your links have been saved.');
    }

    private function profileFor(Request $request): Profile
    {
        $collaborator = $request->user()?->collaborator;

        abort_if($collaborator === null, 403, 'Only admitted collaborators have a profile.');

        return $collaborator->profile()->firstOrCreate([]);
    }
}
