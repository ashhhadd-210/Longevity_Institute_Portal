<?php

namespace App\Support;

use App\Models\Invitation;
use App\Models\User;

/**
 * How the Portal addresses a person, in one place (agreed with the Institute,
 * 28 August 2026).
 *
 * Everything that speaks to someone comes through here: invitations, admission
 * messages, information requests, announcements when they are built, and the
 * Portal's own greeting when a collaborator signs in. One rule written once,
 * so that two messages can never address the same person differently.
 *
 * The chain is preferred name, then first name, then a courteous greeting with
 * no name at all. That last step is the one that matters in practice: before
 * someone registers the Portal may hold only what the Founder typed, and it
 * must never send "Hello ,".
 */
final class Salutation
{
    /**
     * The greeting line for a message, without trailing punctuation.
     */
    public static function greeting(User|Invitation|string|null $addressee, string $opening = 'Hello'): string
    {
        $name = self::name($addressee);

        return $name === null ? $opening : $opening.' '.$name;
    }

    /**
     * The name to address this person by, or null when the Portal does not
     * confidently know one.
     */
    public static function name(User|Invitation|string|null $addressee): ?string
    {
        $name = match (true) {
            $addressee instanceof User, $addressee instanceof Invitation => $addressee->salutationName(),
            is_string($addressee) => $addressee,
            default => null,
        };

        $name = $name === null ? null : trim($name);

        return ($name === null || $name === '') ? null : $name;
    }
}
