<?php

namespace App\Models;

use App\Enums\CollaboratorStatus;
use App\Enums\VisibilityOverride;
use Database\Factories\CollaboratorFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Institutional membership. Admitted is not the same as directory-visible:
 * visibility is gated by profile completion, with an audited administrative
 * override in either direction (doc 02 §4).
 *
 * @property CollaboratorStatus $status
 * @property VisibilityOverride|null $visibility_override
 * @property bool $directory_visible
 */
class Collaborator extends Model
{
    /** @use HasFactory<CollaboratorFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'invitation_id',
        'status',
        'cohort',
        'directory_visible',
        'visibility_override',
        'admitted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => CollaboratorStatus::class,
            'directory_visible' => 'boolean',
            'visibility_override' => VisibilityOverride::class,
            'admitted_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Invitation, $this> */
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    /** @return HasOne<Profile, $this> */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    /** @return BelongsToMany<ExpertiseArea, $this> */
    public function expertiseAreas(): BelongsToMany
    {
        return $this->belongsToMany(ExpertiseArea::class, 'collaborator_expertise');
    }

    /** @return HasMany<Referral, $this> */
    public function referralsMade(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_collaborator_id');
    }

    /**
     * The single answer to "does this collaborator appear in the directory?".
     * Directory queries scope on this logic at the query layer so a template
     * mistake can never leak a hidden profile (doc 06 §4).
     */
    public function isDirectoryListed(): bool
    {
        if ($this->status !== CollaboratorStatus::Active) {
            return false;
        }

        return match ($this->visibility_override) {
            VisibilityOverride::ForceHidden => false,
            VisibilityOverride::ForceVisible => true,
            null => $this->directory_visible,
        };
    }

    /** @param Builder<Collaborator> $query */
    public function scopeDirectoryListed(Builder $query): void
    {
        $query->where('status', CollaboratorStatus::Active->value)
            ->where(function (Builder $q): void {
                $q->where('visibility_override', VisibilityOverride::ForceVisible->value)
                    ->orWhere(function (Builder $inner): void {
                        $inner->whereNull('visibility_override')
                            ->where('directory_visible', true);
                    });
            });
    }
}
