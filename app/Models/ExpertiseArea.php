<?php

namespace App\Models;

use Database\Factories\ExpertiseAreaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Institute-controlled vocabulary, never free text (ADR-008). Managed by the
 * Institute; collaborators select from it.
 */
class ExpertiseArea extends Model
{
    /** @use HasFactory<ExpertiseAreaFactory> */
    use HasFactory;

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

    /** @return BelongsToMany<Collaborator, $this> */
    public function collaborators(): BelongsToMany
    {
        return $this->belongsToMany(Collaborator::class, 'collaborator_expertise');
    }
}
