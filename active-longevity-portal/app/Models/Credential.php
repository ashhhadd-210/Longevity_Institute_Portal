<?php

namespace App\Models;

use App\Support\VocabularyNormaliser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A professional or academic credential, held as a record rather than as an
 * acronym.
 *
 * That distinction is the whole design. MD is Doctor of Medicine in most of the
 * world and Managing Director in others. FRCP differs between London,
 * Edinburgh, Glasgow and Canada. If the letters were the identifier, those
 * could not coexist, and somebody would end up displaying a credential they do
 * not hold.
 *
 * Because a collaborator selects a record rather than typing text, correcting
 * the wording of one entry corrects every profile using it at once. That is the
 * dividend of a controlled vocabulary, and it is only available if the library
 * exists before the data does.
 */
class Credential extends Model
{
    protected $fillable = [
        'acronym',
        'meaning',
        'jurisdiction',
        'issuing_body',
        'description',
        'credential_type',
        'search_key',
        'approved',
        'proposed_by_user_id',
        'approved_by_user_id',
        'approved_at',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'approved' => 'boolean',
            'active' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // The searchable form is both the letters and the meaning, so somebody
        // who holds a Master of Public Health but does not think of themselves
        // as an MPH still finds it by typing "public health".
        static::saving(function (Credential $credential): void {
            $credential->search_key = VocabularyNormaliser::credentialKey(
                (string) $credential->acronym,
                (string) $credential->meaning,
                $credential->jurisdiction,
            );
        });
    }

    /** @return BelongsToMany<Profile, $this> */
    public function profiles(): BelongsToMany
    {
        return $this->belongsToMany(Profile::class, 'collaborator_credentials');
    }

    /** @return BelongsTo<User, $this> */
    public function proposedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_by_user_id');
    }

    /**
     * What a collaborator may choose from. A proposed credential is not offered
     * to anybody else until the Institute has approved it.
     *
     * @param  Builder<Credential>  $query
     * @return Builder<Credential>
     */
    public function scopeSelectable(Builder $query): Builder
    {
        return $query->where('approved', true)->where('active', true);
    }

    /**
     * How this reads beside the letters when somebody is choosing. The meaning
     * is what disambiguates one MD from another.
     */
    public function describe(): string
    {
        return $this->jurisdiction === null
            ? $this->meaning
            : $this->meaning.' ('.$this->jurisdiction.')';
    }
}
