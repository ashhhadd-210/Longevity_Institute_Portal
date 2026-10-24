<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Services\Profiles\ProfilePhotos;
use Error;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use League\Flysystem\FilesystemException;

/**
 * A collaborator's photograph.
 *
 * The rules a machine can check are enforced here. The rules a person checks
 * belong to the Institute's review, and the guidance at the point of upload
 * simply tells the collaborator what those are so that nobody is surprised.
 */
class ProfilePhotoController extends Controller
{
    public function __construct(private readonly ProfilePhotos $photos) {}

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'file', 'max:8192'],
        ], [
            'photo.required' => 'Please choose a photograph to upload.',
            'photo.max' => 'That photograph is larger than 8 MB.',
        ]);

        $file = $request->file('photo');
        abort_if($file === null || is_array($file), 422);

        try {
            $this->photos->store($this->profileFor($request), $file);
        } catch (InvalidArgumentException $e) {
            return redirect()->route('profile.edit')->withErrors(['photo' => $e->getMessage()]);
        } catch (Error|FilesystemException $e) {
            // The photograph was fine; the server was not: an image extension
            // missing, or a storage folder the web server may not write to. That is the Institute's problem
            // to hear about, not the collaborator's to puzzle over.
            report($e);

            return redirect()->route('profile.edit')->withErrors([
                'photo' => 'The Portal could not process photographs just now. The Institute has been told; please try again later.',
            ]);
        }

        return redirect()->route('profile.edit')->with('status', 'Your photograph has been saved.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->photos->remove($this->profileFor($request));

        return redirect()->route('profile.edit')->with('status', 'Your photograph has been removed.');
    }

    private function profileFor(Request $request): Profile
    {
        $collaborator = $request->user()?->collaborator;

        abort_if($collaborator === null, 403, 'Only admitted collaborators have a profile.');

        return $collaborator->profile()->firstOrCreate([]);
    }
}
