<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Something a collaborator wanted the Institute to know about the Portal.
 *
 * Needed before Alpha, because the first eight participants are the most
 * valuable readers the Portal will ever have, and they should have somewhere to
 * say what they find. Records which page they were on when they said it, since
 * "the search is confusing" means something different on the directory than on
 * the classification screen.
 */
class FeedbackNote extends Model
{
    protected $fillable = [
        'user_id',
        'message',
        'page',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
