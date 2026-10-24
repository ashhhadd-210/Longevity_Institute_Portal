<?php

namespace App\Enums;

/**
 * Who may see a piece of information, as agreed with the Institute on
 * 29 September 2026 and confirmed in writing.
 *
 * A ladder rather than a switch, most protected first. The fourth rung exists
 * and stands empty: nothing in the Portal is visible on the open web today. It
 * is there so that if the Institute ever decides something should be, that is a
 * setting rather than a rebuild, and rewrites of visibility logic are precisely
 * where privacy accidents happen.
 *
 * Two separate questions govern every field. This is the first, who may see it.
 * The second, who decides, is the ceiling and the collaborator's choice within
 * it: a collaborator may always narrow, never widen.
 */
enum Audience: int
{
    /** The Founder and administrators. Nobody else, ever. */
    case Institute = 1;

    /** The person themselves, and the Institute. */
    case Collaborator = 2;

    /** Everybody admitted to the collaborator community. */
    case Community = 3;

    /**
     * Anyone at all, signed in or not.
     *
     * Deliberately unused. No field is placed here.
     */
    case Public = 4;

    public function label(): string
    {
        return match ($this) {
            self::Institute => 'The Institute only',
            self::Collaborator => 'The collaborator and the Institute',
            self::Community => 'Admitted collaborators',
            self::Public => 'Anyone on the web',
        };
    }

    /**
     * Whether a viewer standing at this level may see a field reserved for the
     * given audience.
     *
     * Note that the ladder counts audience *width*, not privilege, and the two
     * run opposite ways. Institute is the narrowest audience and therefore the
     * most privileged viewer: a field reserved for the Institute is the hardest
     * to see, and somebody who is the Institute sees everything below it too.
     * Hence the comparison reads the way it does.
     */
    public function canSee(self $required): bool
    {
        return $this->value <= $required->value;
    }

    /**
     * The more restrictive of two audiences.
     *
     * This is how the two locks combine. The Institute sets a ceiling, the
     * collaborator chooses within it, and whichever is tighter wins, so a
     * collaborator can never reach past the limit the Institute set.
     */
    public function narrowestOf(self $other): self
    {
        return $this->value <= $other->value ? $this : $other;
    }
}
