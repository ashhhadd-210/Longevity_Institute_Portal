<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Up to five external links per profile, each with a one-sentence description
 * so another collaborator knows what they are about to open. The cap is
 * enforced in the database (position 1–5, unique per profile).
 */
class ProfileLink extends Model
{
    public const MAX_PER_PROFILE = 5;

    protected $fillable = [
        'profile_id',
        'url',
        'description',
        'position',
    ];

    /** @return BelongsTo<Profile, $this> */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
