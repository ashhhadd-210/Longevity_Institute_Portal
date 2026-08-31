<?php

namespace App\Models;

use App\Enums\InvitationChannel;
use App\Enums\InvitationStatus;
use Database\Factories\InvitationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $email
 * @property string $first_name
 * @property string|null $preferred_name
 * @property string|null $middle_name
 * @property string $last_name
 * @property string|null $name_suffix
 * @property string $token_hash
 * @property string|null $personal_message
 * @property int|null $candidate_case_id
 * @property int|null $referrer_collaborator_id
 * @property int $send_count
 * @property InvitationStatus $status
 * @property InvitationChannel $delivery_channel
 * @property Carbon $expires_at
 * @property Carbon $created_at
 * @property Carbon|null $accepted_at
 * @property Carbon|null $revoked_at
 */
class Invitation extends Model
{
    /** @use HasFactory<InvitationFactory> */
    use HasFactory;

    protected $fillable = [
        'email',
        'delivery_channel',
        'first_name',
        'preferred_name',
        'middle_name',
        'last_name',
        'name_suffix',
        'token_hash',
        'expires_at',
        'status',
        'admission_decision_id',
        'candidate_case_id',
        'referrer_collaborator_id',
        'personal_message',
        'issued_by',
        'send_count',
        'last_sent_at',
        'accepted_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => InvitationStatus::class,
            'delivery_channel' => InvitationChannel::class,
            'expires_at' => 'datetime',
            'last_sent_at' => 'datetime',
            'accepted_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    /**
     * 256-bit random token. Only its SHA-256 hash is persisted; the raw value
     * exists solely inside the invitation link (doc 08 §1).
     *
     * @return array{raw: string, hash: string}
     */
    public static function generateToken(): array
    {
        $raw = bin2hex(random_bytes(32));

        return ['raw' => $raw, 'hash' => hash('sha256', $raw)];
    }

    public static function hashToken(string $raw): string
    {
        return hash('sha256', $raw);
    }

    public function isAcceptable(): bool
    {
        return $this->status->isAcceptable() && ! $this->expires_at->isPast();
    }

    /**
     * Why an invitation cannot be accepted, in words fit to show the person
     * holding the link. They are never shown an error: arriving at a link that
     * has lapsed is not a mistake they made.
     */
    public function unacceptableReason(): ?string
    {
        if ($this->isAcceptable()) {
            return null;
        }

        return match (true) {
            $this->status === InvitationStatus::Accepted => 'already_registered',
            $this->status === InvitationStatus::Revoked => 'withdrawn',
            default => 'lapsed',
        };
    }

    /**
     * How this person should be addressed: preferred name, then first name,
     * then nothing at all rather than something awkward (agreed 28 August
     * 2026). The same rule the User model applies, so an invitation and the
     * account it becomes greet the same person identically.
     */
    public function salutationName(): ?string
    {
        return filled($this->preferred_name)
            ? $this->preferred_name
            : (filled($this->first_name) ? $this->first_name : null);
    }

    public function fullName(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->name_suffix,
        ])));
    }

    /** @return BelongsTo<AdmissionDecision, $this> */
    public function admissionDecision(): BelongsTo
    {
        return $this->belongsTo(AdmissionDecision::class);
    }

    /** @return BelongsTo<CandidateCase, $this> */
    public function candidateCase(): BelongsTo
    {
        return $this->belongsTo(CandidateCase::class);
    }

    /** @return BelongsTo<Collaborator, $this> */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'referrer_collaborator_id');
    }

    /** @return BelongsTo<User, $this> */
    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /** @return HasOne<Collaborator, $this> */
    public function collaborator(): HasOne
    {
        return $this->hasOne(Collaborator::class);
    }
}
