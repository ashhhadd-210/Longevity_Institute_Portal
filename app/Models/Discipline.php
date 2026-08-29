<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * The upper level of the two-level expertise vocabulary (agreed 18 August
 * 2026). A collaborator selects specialties only and the discipline follows,
 * so the two levels can never contradict one another.
 *
 * The shape is fixed before Milestone 3. The contents remain the Institute's
 * to grow indefinitely, and are built from the questions longer lives raise
 * rather than from academic departments.
 */
class Discipline extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    /** @return HasMany<ExpertiseArea, $this> */
    public function specialties(): HasMany
    {
        return $this->hasMany(ExpertiseArea::class);
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
