<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Alternative professional terminology that leads to a controlled term.
 *
 * An alias surfaces a Field or Specialty for the collaborator to consider. It
 * never selects one, and it is never displayed: somebody typing Physiotherapy
 * finds Physical Therapy, and their profile then says Physical Therapy. That is
 * what keeps the vocabulary consistent while the search behaves like a person.
 *
 * An alias resolves to an existing record and must never create a duplicate
 * one, which the database enforces by insisting that exactly one of the two
 * targets is set.
 *
 * Ambiguous abbreviations are kept but flagged. PT might mean Physical Therapy
 * or several other things, so it returns all its candidates ranked below exact
 * and true-alias matches rather than confidently resolving to one. An alias
 * that sends somebody confidently to the wrong place is worse than no alias,
 * because they do not know they have been misdirected.
 */
class ExpertiseAlias extends Model
{
    protected $fillable = [
        'term',
        'search_key',
        'discipline_id',
        'expertise_area_id',
        'ambiguous',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'ambiguous' => 'boolean',
            'active' => 'boolean',
        ];
    }

    /** @return BelongsTo<Discipline, $this> */
    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    /** @return BelongsTo<ExpertiseArea, $this> */
    public function expertiseArea(): BelongsTo
    {
        return $this->belongsTo(ExpertiseArea::class);
    }

    /** Whether this alias points at a Field rather than a Specialty. */
    public function pointsToField(): bool
    {
        return $this->discipline_id !== null;
    }
}
