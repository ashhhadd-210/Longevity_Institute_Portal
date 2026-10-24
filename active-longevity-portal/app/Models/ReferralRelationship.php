<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * The Institute-controlled vocabulary for how a referrer knows the person they
 * recommend (agreed 27 August 2026). Contents are the Institute's to grow.
 */
class ReferralRelationship extends Model
{
    protected $fillable = [
        'key',
        'label',
        'requires_detail',
        'active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'requires_detail' => 'boolean',
            'active' => 'boolean',
        ];
    }

    /** @return HasMany<Referral, $this> */
    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class);
    }

    /**
     * @param  Builder<ReferralRelationship>  $query
     * @return Builder<ReferralRelationship>
     */
    public function scopeSelectable(Builder $query): Builder
    {
        return $query->where('active', true)->orderBy('sort_order')->orderBy('label');
    }
}
