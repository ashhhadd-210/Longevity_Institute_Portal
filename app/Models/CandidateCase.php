<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * One human being, however many times they reached the Institute.
 *
 * A person may apply unprompted while a collaborator independently recommends
 * them. Both submissions are kept because each carries something the other
 * lacks, but they converge here so that there is one decision, one invitation
 * and one admission history (agreed 18 August 2026).
 *
 * @property int $id
 * @property string|null $email
 * @property string $display_name
 * @property int|null $linked_by
 * @property Carbon|null $linked_at
 */
class CandidateCase extends Model
{
    protected $fillable = [
        'email',
        'display_name',
        'linked_by',
        'linked_at',
    ];

    protected function casts(): array
    {
        return [
            'linked_at' => 'datetime',
        ];
    }

    /** @return HasMany<Application, $this> */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /** @return HasMany<Referral, $this> */
    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class);
    }

    /** @return HasMany<AdmissionDecision, $this> */
    public function decisions(): HasMany
    {
        return $this->hasMany(AdmissionDecision::class);
    }

    /** @return HasMany<Invitation, $this> */
    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    /** @return BelongsTo<User, $this> */
    public function linkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'linked_by');
    }

    /**
     * An invitation that is outstanding or already accepted. Its existence is
     * what blocks a second invitation for the same person: a referral for
     * someone already invited or admitted shows the existing case instead.
     */
    public function liveInvitation(): ?Invitation
    {
        return $this->invitations()
            ->whereIn('status', ['issued', 'sent', 'accepted'])
            ->latest('id')
            ->first();
    }

    public function isAdmitted(): bool
    {
        return $this->invitations()->where('status', 'accepted')->exists();
    }
}
