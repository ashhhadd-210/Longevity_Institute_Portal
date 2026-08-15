<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Database\Factories\ApplicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Application extends Model
{
    /** @use HasFactory<ApplicationFactory> */
    use HasFactory;

    protected $fillable = [
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
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'expertise_claimed' => 'array',
            'submitted_at' => 'datetime',
        ];
    }

    /** @return MorphOne<AdmissionDecision, $this> */
    public function decision(): MorphOne
    {
        return $this->morphOne(AdmissionDecision::class, 'subject');
    }
}
