<?php

namespace App\Support;

/**
 * One result offered to a collaborator who is classifying themselves.
 *
 * Always labeled as a Field or a Specialty, because identical or similar
 * wording exists at both levels and somebody choosing between them must be able
 * to see which is which. Architecture is a Specialty; Architecture & Urban
 * Design is a Field.
 *
 * Where the match was found through an alias, the alias is remembered only so
 * the person can see why this appeared. The term shown, and the term stored, is
 * always the controlled one.
 */
final readonly class ExpertiseMatch
{
    public const KIND_FIELD = 'field';

    public const KIND_SPECIALTY = 'specialty';

    public function __construct(
        public string $kind,
        public int $id,
        public string $name,
        public ?string $descriptor = null,
        /** The alias that surfaced this, when it was not found by its own name. */
        public ?string $matchedVia = null,
        /** Lower sorts first. Exact names beat aliases; ambiguous ones come last. */
        public int $rank = 50,
        /** Fields this Specialty is recognizable within, for context only. */
        public string $context = '',
    ) {}

    public function isField(): bool
    {
        return $this->kind === self::KIND_FIELD;
    }

    public function label(): string
    {
        return $this->isField() ? 'Field' : 'Specialty';
    }

    /**
     * Why this appeared, when it was not obvious. Shown quietly beside the
     * result so that finding Physical Therapy after typing Physiotherapy does
     * not feel like the Portal ignoring what was asked for.
     */
    public function explanation(): ?string
    {
        if ($this->matchedVia === null) {
            return null;
        }

        return 'also known as '.$this->matchedVia;
    }
}
