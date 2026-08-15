<?php

namespace App\Models;

use Database\Factories\ProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Professional presentation. Country is public; city and state/region are
 * administrative-only, a distinction explained to the collaborator at profile
 * completion (client standard). Admin-only fields are exposed exclusively
 * through admin resources — never in directory or public payloads.
 */
class Profile extends Model
{
    /** @use HasFactory<ProfileFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'collaborator_id',
        'professional_title',
        'one_line_summary',
        'about',
        'photo_path',
        'credentials',
        'country_code',
        'city',
        'state_region',
        'languages',
        'collaboration_availability',
        'organisation_id',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'credentials' => 'array',
            'languages' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Collaborator, $this> */
    public function collaborator(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class);
    }

    /** @return BelongsTo<Organisation, $this> */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    /** @return HasMany<ProfileEntry, $this> */
    public function entries(): HasMany
    {
        return $this->hasMany(ProfileEntry::class)->orderBy('section')->orderBy('position');
    }

    /** @return HasMany<ProfileLink, $this> */
    public function links(): HasMany
    {
        return $this->hasMany(ProfileLink::class)->orderBy('position');
    }

    /**
     * Required elements for directory eligibility (doc 02 §4). Final criteria
     * are confirmed with the Institute before Milestone 3; the mechanism is
     * foundational.
     */
    public function hasRequiredElements(): bool
    {
        return $this->professional_title !== null
            && $this->about !== null
            && $this->photo_path !== null
            && $this->country_code !== null;
    }
}
