<?php

namespace App\Models;

use App\Enums\DataRequestStatus;
use App\Enums\DataRequestType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataRequest extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'status',
        'requested_at',
        'completed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => DataRequestType::class,
            'status' => DataRequestStatus::class,
            'requested_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
