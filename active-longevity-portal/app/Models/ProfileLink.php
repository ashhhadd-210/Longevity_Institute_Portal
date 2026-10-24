<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Up to five external links per profile, position 1 to 5, unique per profile
 * and enforced in the database.
 *
 * Each carries a short label the collaborator writes themselves, and that label
 * is what the profile shows, as the clickable text. The address is never
 * printed, though the browser reveals it on hover, which is what makes showing
 * only the label safe. The label replaced the earlier one-sentence explanation
 * rather than joining it.
 *
 * The collaborator sets the order, so Link 1 appears first and there is no
 * Institute-wide standard order. Unused slots vanish from the published profile
 * rather than appearing as empty rows.
 */
class ProfileLink extends Model
{
    public const MAX_PER_PROFILE = 5;

    public const MAX_LABEL = 50;

    protected $fillable = [
        'profile_id',
        'url',
        'label',
        'position',
    ];

    /** @return BelongsTo<Profile, $this> */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
