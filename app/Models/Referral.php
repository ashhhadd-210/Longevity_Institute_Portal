<?php

namespace App\Models;

use App\Enums\ReferralStatus;
use Database\Factories\ReferralFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $candidate_case_id
 * @property int|null $referrer_collaborator_id
 * @property string $candidate_email
 * @property string $candidate_first_name
 * @property string $candidate_last_name
 * @property string|null $candidate_organisation
 * @property string|null $candidate_title
 * @property string|null $candidate_country_code
 * @property string|null $reason
 * @property int|null $referral_relationship_id
 * @property string|null $relationship_detail
 * @property string|null $acquaintance_note
 * @property ReferralStatus $status
 * @property Carbon $created_at
 * @property Carbon|null $set_aside_at
 * @property string|null $set_aside_reason
 */
class Referral extends Model
{
    /** @use HasFactory<ReferralFactory> */
    use HasFactory;

    protected $fillable = [
        'candidate_case_id',
        'referrer_collaborator_id',
        'candidate_email',
        'candidate_first_name',
        'candidate_last_name',
        'candidate_organisation',
        'candidate_title',
        'candidate_country_code',
        'candidate_expertise',
        'reason',
        'referral_relationship_id',
        'relationship_detail',
        'acquaintance_note',
        'comments',
        'status',
        'set_aside_at',
        'set_aside_reason',
        'set_aside_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReferralStatus::class,
            'candidate_expertise' => 'array',
            'set_aside_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Collaborator, $this> */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'referrer_collaborator_id');
    }

    /** @return BelongsTo<CandidateCase, $this> */
    public function candidateCase(): BelongsTo
    {
        return $this->belongsTo(CandidateCase::class);
    }

    /**
     * How the referrer knows this person. Institute-only, permanently: it is a
     * private statement about someone who does not yet know they are being
     * discussed, and it never reaches the candidate or a profile.
     *
     * @return BelongsTo<ReferralRelationship, $this>
     */
    public function relationship(): BelongsTo
    {
        return $this->belongsTo(ReferralRelationship::class, 'referral_relationship_id');
    }

    /** @return BelongsTo<User, $this> */
    public function setAsideBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'set_aside_by');
    }

    /** @return MorphOne<AdmissionDecision, $this> */
    public function decision(): MorphOne
    {
        return $this->morphOne(AdmissionDecision::class, 'subject');
    }

    /** @return MorphMany<InformationRequest, $this> */
    public function informationRequests(): MorphMany
    {
        return $this->morphMany(InformationRequest::class, 'subject');
    }

    public function candidateFullName(): string
    {
        return trim($this->candidate_first_name.' '.$this->candidate_last_name);
    }

    /**
     * The relationship as it should read to the Institute, with the free-text
     * detail folded in where the chosen option asked for one.
     */
    public function relationshipLabel(): ?string
    {
        if ($this->relationship === null) {
            return null;
        }

        if ($this->relationship->requires_detail && filled($this->relationship_detail)) {
            return $this->relationship->label.': '.$this->relationship_detail;
        }

        return $this->relationship->label;
    }
}
