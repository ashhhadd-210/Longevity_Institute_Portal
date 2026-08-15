<?php

namespace App\Models;

use App\Enums\ReferralStatus;
use Database\Factories\ReferralFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Referral extends Model
{
    /** @use HasFactory<ReferralFactory> */
    use HasFactory;

    protected $fillable = [
        'referrer_collaborator_id',
        'candidate_email',
        'candidate_first_name',
        'candidate_last_name',
        'candidate_organisation',
        'candidate_title',
        'candidate_country_code',
        'candidate_expertise',
        'reason',
        'relationship_to_referrer',
        'comments',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReferralStatus::class,
            'candidate_expertise' => 'array',
        ];
    }

    /** @return BelongsTo<Collaborator, $this> */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'referrer_collaborator_id');
    }

    /** @return MorphOne<AdmissionDecision, $this> */
    public function decision(): MorphOne
    {
        return $this->morphOne(AdmissionDecision::class, 'subject');
    }
}
