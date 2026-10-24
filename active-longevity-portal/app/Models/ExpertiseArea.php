<?php

namespace App\Models;

use Database\Factories\ExpertiseAreaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A **Specialty**: optional professional precision beneath the Field level.
 *
 * The table keeps its Milestone 1 name. Every screen says Specialty.
 *
 * Specialties are optional and chosen independently of Fields. A Specialty
 * relates to as many Fields as genuinely apply, and those relationships serve
 * search and discovery only: they never assign a collaborator a Field they did
 * not choose (agreed 17 September 2026).
 *
 * Institute-controlled, never free text (ADR-008). Where no term fits, the
 * collaborator writes their own and continues; nothing is ever a dead end.
 */
class ExpertiseArea extends Model
{
    /** @use HasFactory<ExpertiseAreaFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'search_key',
        'active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    /** @return BelongsToMany<Collaborator, $this> */
    public function collaborators(): BelongsToMany
    {
        return $this->belongsToMany(Collaborator::class, 'collaborator_expertise');
    }

    /**
     * The Fields within which this Specialty is professionally recognizable.
     *
     * **For search only.** Health Policy sits under both Public Policy and
     * Public Health so that either search finds the person, not so that either
     * label is applied to them.
     *
     * @return BelongsToMany<Discipline, $this>
     */
    public function disciplines(): BelongsToMany
    {
        return $this->belongsToMany(Discipline::class, 'discipline_expertise_area');
    }

    /**
     * The collaborators who have claimed this Specialty for themselves.
     *
     * @return BelongsToMany<Profile, $this>
     */
    public function profiles(): BelongsToMany
    {
        return $this->belongsToMany(Profile::class, 'profile_specialties')->withPivot('position');
    }

    /** @return HasMany<ExpertiseAlias, $this> */
    public function aliases(): HasMany
    {
        return $this->hasMany(ExpertiseAlias::class);
    }

    /**
     * @param  Builder<ExpertiseArea>  $query
     * @return Builder<ExpertiseArea>
     */
    public function scopeSelectable(Builder $query): Builder
    {
        return $query->where('active', true)->orderBy('sort_order')->orderBy('name');
    }
}
