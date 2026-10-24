<?php

namespace App\Enums;

/**
 * How strongly the Portal believes two records describe the same person
 * (agreed with the Institute, 18 August 2026).
 *
 * Neither level ever merges anything. A confident match is still shown to a
 * reviewer for confirmation, because deciding that two records are one human
 * being belongs to the Institute, not to the software. The levels differ only
 * in how the suggestion is worded and how prominently it appears.
 */
enum MatchConfidence: string
{
    /** The same email address. Strong, but still hers to confirm. */
    case Confident = 'confident';

    /** The same name. Common names are common; this is a possibility only. */
    case Possible = 'possible';

    public function label(): string
    {
        return match ($this) {
            self::Confident => 'Same email address',
            self::Possible => 'Same name',
        };
    }

    public function explanation(): string
    {
        return match ($this) {
            self::Confident => 'These records share an email address, so they are very likely the same person. Please confirm before they are joined.',
            self::Possible => 'These records share a name. That may be coincidence, so please look before joining them.',
        };
    }
}
