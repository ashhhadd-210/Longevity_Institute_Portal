<?php

namespace App\Support;

use App\Enums\MatchConfidence;
use App\Models\CandidateCase;

/**
 * A suggestion that two records may describe the same human being.
 *
 * A suggestion and nothing more. Joining two cases is always a person's
 * judgment, because a wrongly joined pair is far harder to repair than an
 * unjoined one.
 */
final readonly class DuplicateMatch
{
    public function __construct(
        public CandidateCase $case,
        public MatchConfidence $confidence,
        /**
         * Why this match cannot be joined, or null when it can. A case that has
         * already been judged cannot absorb another, since that would fold a
         * decision into a case it was never made about.
         */
        public ?string $settled = null,
    ) {}

    public function isJoinable(): bool
    {
        return $this->settled === null;
    }
}
