<?php

namespace App\Models;

use App\Enums\ProfileEntrySection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileEntry extends Model
{
    protected $fillable = [
        'profile_id',
        'section',
        'position',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'section' => ProfileEntrySection::class,
            'payload' => 'array',
        ];
    }

    /** @return BelongsTo<Profile, $this> */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
