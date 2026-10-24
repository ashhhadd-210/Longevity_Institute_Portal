<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One collaborator saying to another, through the Institute, that they would
 * like to work together.
 *
 * What the first Beta uses instead of messaging. A record and a note, not a
 * conversation. It is registered against both people and the Institute sees it,
 * which at Alpha is how it is passed on. One approach per pair: interest is
 * registered, not repeated.
 */
class CollaborationInterest extends Model
{
    protected $fillable = [
        'from_profile_id',
        'to_profile_id',
        'note',
    ];

    /** @return BelongsTo<Profile, $this> */
    public function from(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'from_profile_id');
    }

    /** @return BelongsTo<Profile, $this> */
    public function to(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'to_profile_id');
    }
}
