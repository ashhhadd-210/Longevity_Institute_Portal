<?php

namespace App\Support\Visibility;

use App\Enums\Audience;

/**
 * One piece of information the Portal holds about a person, and the rules
 * governing who may see it.
 *
 * The ceiling is the Institute's, and it is not arbitrary preference. It comes
 * from the promise made when the information was collected: a field may never
 * later be shown to a wider audience than the person was told when they gave
 * it. That is why city and state stay with the Institute and are not the
 * collaborator's to widen, however willing an individual might be. The
 * assurance was given to everybody collectively.
 *
 * Where the collaborator has a say, they choose within that ceiling and the
 * narrower of the two wins.
 */
final readonly class FieldVisibility
{
    public function __construct(
        /** How this reads in the table the Institute approves. */
        public string $label,
        /** The widest audience the Institute permits for this field. */
        public Audience $ceiling,
        /** Whether the collaborator may narrow it further. */
        public bool $collaboratorMayNarrow = false,
        /** Where the ceiling comes from, in plain words. */
        public string $because = '',
    ) {}

    /**
     * The audience that actually applies, once the collaborator's own choice is
     * taken into account. Narrower always wins.
     */
    public function effective(?Audience $collaboratorChoice): Audience
    {
        if ($collaboratorChoice === null || ! $this->collaboratorMayNarrow) {
            return $this->ceiling;
        }

        return $this->ceiling->narrowestOf($collaboratorChoice);
    }

    public function decidedBy(): string
    {
        return $this->collaboratorMayNarrow
            ? 'The Institute sets the limit; the collaborator may show less'
            : 'The Institute';
    }
}
