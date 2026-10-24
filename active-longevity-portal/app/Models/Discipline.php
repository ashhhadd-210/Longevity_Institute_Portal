<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A **Field**: the broad professional home a collaborator recognizes as theirs.
 *
 * The table keeps the name it was given in Milestone 1, when the Institute
 * still called these disciplines. Every screen says Field.
 *
 * The test a Field must pass is the Institute's own: would an accomplished
 * professional recognize this as a legitimate professional home? That is why
 * the Register carries Insurance, Pensions and Housing alongside Medicine, and
 * why it does not carry roles, sectors or research topics.
 *
 * Fields are chosen directly, and independently of Specialties. Selecting a
 * Specialty never assigns one (agreed 17 September 2026), because a journalist
 * who writes about health policy is a journalist.
 */
class Discipline extends Model
{
    protected $fillable = [
        'name',
        'descriptor',
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

    /**
     * Specialties professionally recognizable within this Field.
     *
     * **For search and discovery only.** Somebody searching Epidemiology finds
     * the collaborator who chose Nutritional Epidemiology, without that person
     * ever being labeled an epidemiologist. A search relationship, never an
     * identity claim.
     *
     * @return BelongsToMany<ExpertiseArea, $this>
     */
    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(ExpertiseArea::class, 'discipline_expertise_area');
    }

    /**
     * The collaborators who have claimed this Field for themselves.
     *
     * @return BelongsToMany<Profile, $this>
     */
    public function profiles(): BelongsToMany
    {
        return $this->belongsToMany(Profile::class, 'profile_fields')->withPivot('position');
    }

    /**
     * Alternative professional terminology that leads here. An alias surfaces
     * this Field for the collaborator to consider; it never selects it.
     *
     * @return HasMany<ExpertiseAlias, $this>
     */
    public function aliases(): HasMany
    {
        return $this->hasMany(ExpertiseAlias::class);
    }

    /**
     * @param  Builder<Discipline>  $query
     * @return Builder<Discipline>
     */
    public function scopeSelectable(Builder $query): Builder
    {
        return $query->where('active', true)->orderBy('sort_order')->orderBy('name');
    }
}
