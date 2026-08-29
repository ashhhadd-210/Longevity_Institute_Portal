<?php

namespace App\Support;

use Carbon\CarbonInterface;

/**
 * One moment in a candidate's history, in words fit to be read by the person
 * who made the decisions rather than by a developer.
 */
final readonly class HistoryEntry
{
    public function __construct(
        public CarbonInterface $at,
        public string $summary,
        public ?string $detail = null,
        public ?string $actor = null,
        public bool $isDecision = false,
    ) {}
}
