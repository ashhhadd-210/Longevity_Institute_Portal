<?php

namespace App\Services\Profiles;

use App\Models\Profile;
use App\Services\Governance\AuditRecorder;
use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Photographs, and the division of labor the Institute agreed for them.
 *
 * Software enforces what software can judge: the accepted formats, a maximum
 * size, that the image is not animated, and the rectangular crop. A person
 * enforces what a person can judge, which is head and shoulders, no hats and
 * recency, at the Institute's review step. Neither pretends to do the other's
 * job.
 *
 * Every photograph is normalized to the same portrait rectangle and re-encoded,
 * so the directory reads consistently and nothing a browser might misinterpret
 * survives the upload. Uploads come from phone camera rolls more often than
 * from anywhere else, and those arrive in every orientation and size.
 */
class ProfilePhotos
{
    /** Portrait, three by four. Suits head and shoulders and reads well in a list. */
    public const WIDTH = 600;

    public const HEIGHT = 800;

    public const MAX_BYTES = 8 * 1024 * 1024;

    /** @var array<int, string> */
    public const ACCEPTED = ['image/jpeg', 'image/png', 'image/webp'];

    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * Validate, crop to the agreed rectangle, re-encode and store.
     *
     * @throws InvalidArgumentException when the file is not a still photograph
     *                                  the Portal is willing to publish
     */
    public function store(Profile $profile, UploadedFile $file): string
    {
        $this->guard($file);

        $image = $this->decode($file);

        $cropped = $this->cropToPortrait($image);
        imagedestroy($image);

        $path = 'photos/'.$profile->id.'/'.Str::uuid().'.jpg';

        ob_start();
        imagejpeg($cropped, null, 86);
        $bytes = (string) ob_get_clean();
        imagedestroy($cropped);

        Storage::disk($this->disk())->put($path, $bytes, 'public');

        $previous = $profile->photo_path;
        $profile->forceFill(['photo_path' => $path])->save();

        if ($previous !== null && $previous !== $path) {
            Storage::disk($this->disk())->delete($previous);
        }

        $this->audit->record('profile.photo.replaced', $profile->collaborator?->user, $profile);

        // The photograph is part of Directory readiness.
        app(ProfileEditor::class)->refreshCompletion($profile);

        return $path;
    }

    public function remove(Profile $profile): void
    {
        if ($profile->photo_path === null) {
            return;
        }

        Storage::disk($this->disk())->delete($profile->photo_path);
        $profile->forceFill(['photo_path' => null])->save();

        $this->audit->record('profile.photo.removed', $profile->collaborator?->user, $profile);

        app(ProfileEditor::class)->refreshCompletion($profile);
    }

    private function disk(): string
    {
        return (string) config('portal.photo_disk', 'public');
    }

    private function guard(UploadedFile $file): void
    {
        if ($file->getSize() === false || $file->getSize() > self::MAX_BYTES) {
            throw new InvalidArgumentException('That photograph is larger than 8 MB.');
        }

        // Judge by content, not by the name somebody gave the file.
        $mime = (string) $file->getMimeType();

        if (! in_array($mime, self::ACCEPTED, true)) {
            throw new InvalidArgumentException('Please use a JPEG, PNG or WebP photograph.');
        }

        if ($this->isAnimated($file, $mime)) {
            throw new InvalidArgumentException('Animated images cannot be used as a profile photograph.');
        }
    }

    /**
     * GIF is refused outright by the accepted list. WebP can also animate, and
     * an animated WebP declares itself with an ANIM chunk in the header.
     */
    private function isAnimated(UploadedFile $file, string $mime): bool
    {
        if ($mime !== 'image/webp') {
            return false;
        }

        $head = (string) file_get_contents($file->getRealPath(), false, null, 0, 64);

        return str_contains($head, 'ANIM');
    }

    private function decode(UploadedFile $file): GdImage
    {
        $image = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));

        if ($image === false) {
            throw new InvalidArgumentException('That file could not be read as a photograph.');
        }

        // Phones record orientation rather than rotating pixels, so honor it.
        if (function_exists('exif_read_data') && $file->getMimeType() === 'image/jpeg') {
            $exif = @exif_read_data($file->getRealPath());
            $rotation = match ($exif['Orientation'] ?? 1) {
                3 => 180,
                6 => -90,
                8 => 90,
                default => 0,
            };

            if ($rotation !== 0) {
                $rotated = imagerotate($image, $rotation, 0);
                if ($rotated !== false) {
                    imagedestroy($image);
                    $image = $rotated;
                }
            }
        }

        return $image;
    }

    /**
     * Crop the center of the image to three by four, then scale to the standard
     * size. Center cropping suits head and shoulders, which is what the
     * Institute has asked collaborators to supply.
     */
    private function cropToPortrait(GdImage $source): GdImage
    {
        $w = imagesx($source);
        $h = imagesy($source);
        $target = self::WIDTH / self::HEIGHT;

        if ($w / $h > $target) {
            $cropW = (int) round($h * $target);
            $cropH = $h;
        } else {
            $cropW = $w;
            $cropH = (int) round($w / $target);
        }

        $x = (int) floor(($w - $cropW) / 2);
        $y = (int) floor(($h - $cropH) / 2);

        $out = imagecreatetruecolor(self::WIDTH, self::HEIGHT);

        // A white ground rather than black, in case a PNG carried transparency.
        $white = imagecolorallocate($out, 255, 255, 255);
        imagefill($out, 0, 0, (int) $white);

        imagecopyresampled($out, $source, 0, 0, $x, $y, self::WIDTH, self::HEIGHT, $cropW, $cropH);

        return $out;
    }
}
