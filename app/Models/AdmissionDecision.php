<?php

namespace App\Models;

use App\Enums\AdmissionSubjectType;
use App\Enums\DecisionOutcome;
use Database\Factories\AdmissionDecisionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * The recorded judgement. A referral or application receives a decision; a
 * direct invitation carries a decision with no subject record.
 *
 * @property int $id
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property int|null $candidate_case_id
 * @property DecisionOutcome $decision
 * @property string|null $rationale
 * @property Carbon $decided_at
 */
class AdmissionDecision extends Model
{
    /** @use HasFactory<AdmissionDecisionFactory> */
    use HasFactory;

    protected $fillable = [
        'subject_type',
        'subject_id',
        'candidate_case_id',
        'decision',
        'rationale',
        'decided_by',
        'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'decision' => DecisionOutcome::class,
            'decided_at' => 'datetime',
        ];
    }

    public function subjectType(): AdmissionSubjectType
    {
        // A decision with no subject record is, by definition, a direct
        // invitation; "direct" is derived rather than stored (see migration).
        return $this->subject_type === null
            ? AdmissionSubjectType::Direct
            : AdmissionSubjectType::from($this->subject_type);
    }

    /** @return MorphTo<Model, $this> */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<User, $this> */
    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    /** @return BelongsTo<CandidateCase, $this> */
    public function candidateCase(): BelongsTo
    {
        return $this->belongsTo(CandidateCase::class);
    }

    /** @return HasOne<Invitation, $this> */
    public function invitation(): HasOne
    {
        return $this->hasOne(Invitation::class);
    }
}
