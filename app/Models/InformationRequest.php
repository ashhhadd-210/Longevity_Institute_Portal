<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * A question asked of a candidate or a referrer, and the answer if one came.
 *
 * "Need more information" is a question, not a judgement (agreed 18 August
 * 2026). It keeps the case open and may be asked more than once, which is why
 * it is recorded as its own kind of event: a candidate questioned twice and
 * then approved must not appear to have been decided upon three times.
 *
 * @property int $id
 * @property string $subject_type
 * @property int $subject_id
 * @property string $required
 * @property string|null $response
 * @property Carbon $requested_at
 * @property Carbon|null $responded_at
 */
class InformationRequest extends Model
{
    protected $fillable = [
        'subject_type',
        'subject_id',
        'requested_by',
        'required',
        'requested_at',
        'response',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    /** @return MorphTo<Model, $this> */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<User, $this> */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function isOutstanding(): bool
    {
        return $this->responded_at === null;
    }
}
