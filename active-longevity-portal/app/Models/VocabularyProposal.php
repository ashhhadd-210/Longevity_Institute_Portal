<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A term a collaborator entered because nothing in the vocabulary fitted.
 *
 * The Institute's rule is that classification must never be a dead end. If
 * somebody cannot find an accurate Field, Specialty or credential, they write
 * the words they use, their profile saves, and they continue. Nothing waits on
 * a review, and nobody is left at a form wondering what to do.
 *
 * One mechanism serves all three vocabularies because all three follow the
 * identical lifecycle: search, select, request if missing, the Institute
 * reviews and standardizes, permanent.
 *
 * A proposal is never offered to anybody else while it waits. It belongs to the
 * person who wrote it, and it appears publicly only once approved. An
 * unreviewed term is information about the community, not a failure of the
 * classification system.
 */
class VocabularyProposal extends Model
{
    public const KIND_FIELD = 'field';

    public const KIND_SPECIALTY = 'specialty';

    public const KIND_CREDENTIAL = 'credential';

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_DECLINED = 'declined';

    public const STATUS_MAPPED = 'mapped';

    protected $fillable = [
        'kind',
        'term',
        'meaning',
        'jurisdiction',
        'proposed_by_user_id',
        'profile_id',
        'status',
        'reviewed_by_user_id',
        'reviewed_at',
        'review_note',
        'resolved_discipline_id',
        'resolved_expertise_area_id',
        'resolved_credential_id',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function proposedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_by_user_id');
    }

    /** @return BelongsTo<Profile, $this> */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * @param  Builder<VocabularyProposal>  $query
     * @return Builder<VocabularyProposal>
     */
    public function scopeAwaitingReview(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING)->orderBy('created_at');
    }

    public function isSettled(): bool
    {
        return $this->status !== self::STATUS_PENDING;
    }

    /**
     * What the collaborator is told while it waits. They should know the
     * Institute has it, and that nothing is required of them.
     */
    public function statusForCollaborator(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Awaiting review by the Institute',
            self::STATUS_APPROVED => 'Added to the Institute vocabulary',
            self::STATUS_MAPPED => 'Matched to an existing term',
            default => 'Reviewed by the Institute',
        };
    }
}
