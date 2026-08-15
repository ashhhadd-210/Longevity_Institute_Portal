<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Consent captured against a versioned privacy notice; a version change
 * requires re-consent (doc 08 §3).
 */
class Consent extends Model
{
    protected $fillable = [
        'user_id',
        'document',
        'version',
        'granted_at',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'granted_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
