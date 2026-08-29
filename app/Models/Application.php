<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Database\Factories\ApplicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $candidate_case_id
 * @property string $email
 * @property string $first_name
 * @property string|null $middle_name
 * @property string $last_name
 * @property string|null $organisation_text
 * @property string|null $professional_title
 * @property string|null $country_code
 * @property string|null $motivation
 * @property ApplicationStatus $status
 * @property Carbon $submitted_at
 * @property Carbon $created_at
 * @property Carbon|null $set_aside_at
 * @property string|null $set_aside_reason
 */
class Application extends Model
{
    /** @use HasFactory<ApplicationFactory> */
    use HasFactory;

    protected $fillable = [
        'candidate_case_id',
        'email',
        'first_name',
        'middle_name',
        'last_name',
        'organisation_text',
        'professional_title',
        'country_code',
        'expertise_claimed',
        'motivation',
        'status',
        'submitted_at',
        'set_aside_at',
        'set_aside_reason',
        'set_aside_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'expertise_claimed' => 'array',
            'submitted_at' => 'datetime',
            'set_aside_at' => 'datetime',
        ];
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

    /** @return BelongsTo<CandidateCase, $this> */
    public function candidateCase(): BelongsTo
    {
        return $this->belongsTo(CandidateCase::class);
    }

    /** @return BelongsTo<User, $this> */
    public function setAsideBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'set_aside_by');
    }

    public function fullName(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ])));
    }
}
