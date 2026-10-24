<?php

namespace App\Http\Controllers;

use App\Models\Credential;
use App\Models\Profile;
use App\Services\Expertise\CredentialLibrary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * The credentials shown after a collaborator's name.
 *
 * Up to three, chosen from the approved library rather than typed. Where the
 * library does not hold what somebody has, they request it and carry on; the
 * request form shows near matches first so that nobody proposes Masters in
 * Public Health when Master of Public Health already exists.
 */
class ProfileCredentialController extends Controller
{
    public function __construct(private readonly CredentialLibrary $library) {}

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate(['q' => ['required', 'string', 'max:120']]);

        return response()->json([
            'results' => $this->library->search((string) $validated['q'])->map(fn (Credential $c): array => [
                'id' => $c->id,
                'acronym' => $c->acronym,
                'meaning' => $c->meaning,
                'jurisdiction' => $c->jurisdiction,
                'describe' => $c->describe(),
            ])->all(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'credential_ids' => ['array', 'max:'.CredentialLibrary::MAX_DISPLAYED],
            'credential_ids.*' => ['integer', 'exists:credentials,id'],
        ], [
            'credential_ids.max' => 'You may show up to three credentials after your name.',
        ]);

        try {
            $this->library->setDisplayed(
                $this->profileFor($request),
                array_map('intval', $validated['credential_ids'] ?? []),
            );
        } catch (InvalidArgumentException $e) {
            return redirect()->route('profile.edit')->withErrors(['credential_ids' => $e->getMessage()]);
        }

        return redirect()->route('profile.edit')->with('status', 'Your credentials have been saved.');
    }

    /**
     * Request a credential the library does not hold.
     *
     * The first submission shows any near matches and asks the person to look
     * at them before continuing. The second, confirmed, records the request.
     * Either way their profile is already saved and nothing waits on the
     * Institute.
     */
    public function propose(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'acronym' => ['required', 'string', 'max:40'],
            'meaning' => ['required', 'string', 'max:300'],
            'jurisdiction' => ['nullable', 'string', 'max:120'],
            'confirmed' => ['nullable', 'boolean'],
        ], [], [
            'acronym' => 'letters',
            'meaning' => 'full meaning',
        ]);

        $user = $request->user();
        abort_if($user === null, 403);

        if (! ($validated['confirmed'] ?? false)) {
            $near = $this->library->nearMatches($validated['acronym'], $validated['meaning']);

            if ($near->isNotEmpty()) {
                return redirect()
                    ->route('profile.edit')
                    ->withInput()
                    ->with('credential_near_matches', $near->map(fn (Credential $c): array => [
                        'id' => $c->id,
                        'acronym' => $c->acronym,
                        'describe' => $c->describe(),
                    ])->all());
            }
        }

        $this->library->propose(
            $this->profileFor($request),
            $user,
            $validated['acronym'],
            $validated['meaning'],
            $validated['jurisdiction'] ?? null,
        );

        return redirect()
            ->route('profile.edit')
            ->with('status', 'Thank you. The Institute will look at that credential, and it will appear on your profile once approved. Nothing is waiting on you.');
    }

    private function profileFor(Request $request): Profile
    {
        $collaborator = $request->user()?->collaborator;

        abort_if($collaborator === null, 403, 'Only admitted collaborators have a profile.');

        return $collaborator->profile()->firstOrCreate([]);
    }
}
