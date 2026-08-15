<?php

namespace App\Models;

use App\Enums\InvitationStatus;
use Database\Factories\InvitationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property InvitationStatus $status
 * @property Carbon $expires_at
 */
class Invitation extends Model
{
    /** @use HasFactory<InvitationFactory> */
    use HasFactory;

    protected $fillable = [
        'email',
        'first_name',
        'last_name',
        'token_hash',
        'expires_at',
        'status',
        'admission_decision_id',
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

    /** @return BelongsTo<AdmissionDecision, $this> */
    public function admissionDecision(): BelongsTo
    {
        return $this->belongsTo(AdmissionDecision::class);
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
