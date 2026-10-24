<?php

namespace App\Models;

use App\Enums\CollaboratorStatus;
use App\Enums\ProfileEntrySection;
use Database\Factories\ProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Professional presentation. Country is public; city and state/region are
 * administrative-only, a distinction explained to the collaborator at profile
 * completion (client standard). Admin-only fields are exposed exclusively
 * through admin resources — never in directory or public payloads.
 */
class Profile extends Model
{
    /** @use HasFactory<ProfileFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'collaborator_id',
        'professional_title',
        'one_line_summary',
        'about',
        'photo_path',
        'credentials',
        'country_code',
        'city',
        'state_region',
        'languages',
        'collaboration_availability',
        'accepts_collaboration_interest',
        'organisation_id',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'credentials' => 'array',
            'languages' => 'array',
            'accepts_collaboration_interest' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Collaborator, $this> */
    public function collaborator(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class);
    }

    /** @return BelongsTo<Organisation, $this> */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    /**
     * The Fields this collaborator claims as their professional home.
     *
     * Chosen directly and deliberately. Nothing here is ever assigned as a side
     * effect of choosing a Specialty (agreed 17 September 2026), because a
     * journalist who writes about health policy is a journalist.
     *
     * @return BelongsToMany<Discipline, $this>
     */
    public function fields(): BelongsToMany
    {
        return $this->belongsToMany(Discipline::class, 'profile_fields')
            ->withPivot('position')
            ->withTimestamps()
            ->orderBy('profile_fields.position');
    }

    /**
     * Optional professional precision. A collaborator may have none at all;
     * Actuarial Science and Venture Capital are complete identities on their
     * own, and inventing Specialties beneath them would be manufacturing
     * vocabulary nobody uses.
     *
     * @return BelongsToMany<ExpertiseArea, $this>
     */
    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(ExpertiseArea::class, 'profile_specialties')
            ->withPivot('position')
            ->withTimestamps()
            ->orderBy('profile_specialties.position');
    }

    /**
     * Up to three, shown after the name. The limit is enforced by the database
     * rather than by a screen.
     *
     * @return BelongsToMany<Credential, $this>
     */
    public function approvedCredentials(): BelongsToMany
    {
        return $this->belongsToMany(Credential::class, 'collaborator_credentials')
            ->withPivot('position')
            ->withTimestamps()
            ->orderBy('collaborator_credentials.position');
    }

    /**
     * Terms this collaborator entered because nothing in the vocabulary fitted.
     * Their profile saved and they carried on; the Institute decides later.
     *
     * @return HasMany<VocabularyProposal, $this>
     */
    public function vocabularyProposals(): HasMany
    {
        return $this->hasMany(VocabularyProposal::class);
    }

    /**
     * The roles a collaborator holds right now, up to three (the approved
     * addition). Each carries its own title, organisation and department, since
     * senior people frequently hold a chair, a hospital role and an advisory
     * seat at once and none of the three describes the other two.
     *
     * @return HasMany<ProfileEntry, $this>
     */
    public function currentPositions(): HasMany
    {
        return $this->hasMany(ProfileEntry::class)
            ->where('section', ProfileEntrySection::CurrentPosition->value)
            ->orderBy('position');
    }

    /**
     * Approaches from other collaborators who would like to work together.
     *
     * @return HasMany<CollaborationInterest, $this>
     */
    public function interestsReceived(): HasMany
    {
        return $this->hasMany(CollaborationInterest::class, 'to_profile_id');
    }

    /** @return HasMany<ProfileEntry, $this> */
    public function entries(): HasMany
    {
        return $this->hasMany(ProfileEntry::class)->orderBy('section')->orderBy('position');
    }

    /** @return HasMany<ProfileLink, $this> */
    public function links(): HasMany
    {
        return $this->hasMany(ProfileLink::class)->orderBy('position');
    }

    /**
     * Whether this profile appears in the directory and may be opened by other
     * collaborators. Complete, belonging to an active collaborator, and not
     * withheld by the Institute. The one rule, used wherever the question is
     * asked, so the profile screen and the directory can never disagree.
     */
    public function isListable(): bool
    {
        $collaborator = $this->collaborator;

        return $this->completed_at !== null
            && $collaborator !== null
            && $collaborator->directory_visible
            && $collaborator->status === CollaboratorStatus::Active;
    }

    /**
     * Required elements for directory eligibility (doc 02 §4). Final criteria
     * are confirmed with the Institute before Milestone 3; the mechanism is
     * foundational.
     */
    public function hasRequiredElements(): bool
    {
        return $this->professional_title !== null
            && $this->about !== null
            && $this->photo_path !== null
            && $this->country_code !== null;
    }
}
