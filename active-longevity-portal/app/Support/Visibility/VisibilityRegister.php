<?php

namespace App\Support\Visibility;

use App\Enums\Audience;
use RuntimeException;

/**
 * Every piece of information the Portal holds about a person, declared once.
 *
 * This is the single enforcement point agreed with the Institute on 29 August
 * 2026. Visibility is a property of the field itself rather than of whichever
 * page happens to be rendering it, so no future screen can get it wrong by
 * forgetting.
 *
 * It is also the source of the plain-English table the Institute approves. The
 * table is generated from these declarations rather than written alongside them,
 * which is what stops the written policy and the actual behavior drifting apart.
 *
 * It fails safe. A field nobody has declared is treated as belonging to the
 * Institute alone, and `assertComplete()` refuses to pass until somebody states
 * an intention. The mistakes this system makes must always err toward privacy.
 */
final class VisibilityRegister
{
    /**
     * @var array<string, FieldVisibility>|null
     */
    private static ?array $fields = null;

    /**
     * @return array<string, FieldVisibility>
     */
    public static function all(): array
    {
        return self::$fields ??= self::declare();
    }

    public static function for(string $field): FieldVisibility
    {
        // Undeclared means Institute-only. A field nobody thought about is not
        // published by accident.
        return self::all()[$field] ?? new FieldVisibility(
            label: $field,
            ceiling: Audience::Institute,
            because: 'Not yet declared, so it stays with the Institute.',
        );
    }

    /**
     * Whether a viewer at this level may see this field, taking the
     * collaborator's own choice into account.
     */
    public static function visible(string $field, Audience $viewer, ?Audience $collaboratorChoice = null): bool
    {
        return $viewer->canSee(self::for($field)->effective($collaboratorChoice));
    }

    /**
     * Every column the Portal stores about a person must appear above. Called
     * by the test suite, so a new field cannot reach production without a
     * deliberate decision about who may see it.
     *
     * @param  array<int, string>  $storedFields
     */
    public static function assertComplete(array $storedFields): void
    {
        $undeclared = array_values(array_diff($storedFields, array_keys(self::all())));

        if ($undeclared !== []) {
            throw new RuntimeException(
                'These fields have no declared visibility, so the Portal will not publish them: '
                .implode(', ', $undeclared)
                .'. Declare each one in VisibilityRegister.'
            );
        }
    }

    /**
     * @return array<string, FieldVisibility>
     */
    private static function declare(): array
    {
        return [
            // ---------------------------------------------------------- //
            // Name and how a person is addressed.
            // ---------------------------------------------------------- //
            'first_name' => new FieldVisibility(
                'First name', Audience::Community,
                because: 'A collaborator is known to the community by name.'),
            'middle_name' => new FieldVisibility(
                'Middle name', Audience::Community,
                because: 'Part of the name of record where a person gives one.'),
            'last_name' => new FieldVisibility(
                'Family name', Audience::Community,
                because: 'A collaborator is known to the community by name.'),
            'name_suffix' => new FieldVisibility(
                'Generational suffix', Audience::Community,
                because: 'Part of the name, held apart so sorting stays correct.'),
            'preferred_name' => new FieldVisibility(
                'Preferred name', Audience::Community,
                because: 'Given in answer to how the Portal should address them.'),
            'credentials' => new FieldVisibility(
                'Credentials after the name', Audience::Community, true,
                'Chosen for display; the collaborator may show none.'),

            // ---------------------------------------------------------- //
            // Professional standing.
            // ---------------------------------------------------------- //
            'professional_title' => new FieldVisibility(
                'Professional title', Audience::Community,
                because: 'The heart of a directory entry.'),
            'one_line_summary' => new FieldVisibility(
                'One-line summary', Audience::Community,
                because: 'Written for other collaborators to read.'),
            'about' => new FieldVisibility(
                'About your work', Audience::Community, true,
                'Written for the community; the collaborator may leave it blank.'),
            'organisation_id' => new FieldVisibility(
                'Organization', Audience::Community,
                because: 'Professional affiliation is part of standing.'),
            'positions' => new FieldVisibility(
                'Current positions, up to three', Audience::Community,
                because: 'Senior people hold more than one appointment.'),
            'fields' => new FieldVisibility(
                'Fields', Audience::Community,
                because: 'The directory exists so that people can be found by these.'),
            'specialties' => new FieldVisibility(
                'Specialties', Audience::Community,
                because: 'Optional precision, offered for discovery.'),
            'profile_links' => new FieldVisibility(
                'Links', Audience::Community, true,
                'The collaborator chooses which to add and in what order.'),
            'photo_path' => new FieldVisibility(
                'Photograph', Audience::Community, true,
                'The collaborator may choose not to publish one.'),
            'languages' => new FieldVisibility(
                'Languages', Audience::Community, true,
                'Offered to help collaborators speak to one another.'),
            'collaboration_availability' => new FieldVisibility(
                'Open to collaboration', Audience::Community, true,
                'Entirely the collaborator\'s own decision.'),
            'accepts_collaboration_interest' => new FieldVisibility(
                'Whether others may register an interest in working with them', Audience::Community, true,
                'Theirs to switch off. Shown only as the presence or absence of the option.'),
            'collaboration_interests' => new FieldVisibility(
                'Interests registered in working with them', Audience::Collaborator,
                because: 'Seen by the two people concerned and the Institute, which passes it on. Never a public count.'),
            'feedback_notes' => new FieldVisibility(
                'Feedback they gave about the Portal', Audience::Institute,
                because: 'Given to the Institute about the Portal, not about themselves.'),

            // ---------------------------------------------------------- //
            // Location. The promise made at collection binds us here.
            // ---------------------------------------------------------- //
            'country_code' => new FieldVisibility(
                'Country', Audience::Community,
                because: 'Collected as part of the directory and searchable by it.'),
            'city' => new FieldVisibility(
                'City', Audience::Institute,
                because: 'Collected as administrative, and said to be so at the time.'),
            'state_region' => new FieldVisibility(
                'State or region', Audience::Institute,
                because: 'Collected as administrative, and said to be so at the time.'),

            // ---------------------------------------------------------- //
            // The person's own account, never the community's business.
            // ---------------------------------------------------------- //
            'email' => new FieldVisibility(
                'Email address', Audience::Collaborator,
                because: 'The Portal carries messages rather than displaying addresses. An address never displayed cannot be harvested.'),
            'password' => new FieldVisibility(
                'Password', Audience::Institute,
                because: 'Stored only as an irreversible hash and never displayed to anybody.'),
            'two_factor_secret' => new FieldVisibility(
                'Two-factor secret', Audience::Institute,
                because: 'A security credential, never shown.'),

            // ---------------------------------------------------------- //
            // Admission. The Institute's record of its own decisions.
            // ---------------------------------------------------------- //
            'admission_route' => new FieldVisibility(
                'How they came to the Institute', Audience::Institute,
                because: 'Provenance is the Institute\'s record, not a public fact.'),
            'referrer' => new FieldVisibility(
                'Who recommended them', Audience::Institute,
                because: 'Named to the invited person by the Institute if it chooses, never published.'),
            'referral_relationship' => new FieldVisibility(
                'The referrer\'s relationship to them', Audience::Institute,
                because: 'A private statement by one person about another; seen by the Institute alone, permanently.'),
            'referral_note' => new FieldVisibility(
                'How the referrer knows them', Audience::Institute,
                because: 'Given in confidence to inform a decision.'),
            'application_motivation' => new FieldVisibility(
                'Why they applied', Audience::Institute,
                because: 'Written for the admission decision, not for publication.'),
            'review_decisions' => new FieldVisibility(
                'Review decisions and reasoning', Audience::Institute,
                because: 'The Institute\'s own judgments.'),
            'consent_records' => new FieldVisibility(
                'Consent record', Audience::Collaborator,
                because: 'Theirs to see, and the Institute\'s to evidence.'),

            // ---------------------------------------------------------- //
            // Vocabulary the collaborator proposed.
            // ---------------------------------------------------------- //
            'proposed_terms' => new FieldVisibility(
                'Terms they proposed', Audience::Collaborator,
                because: 'Visible to them while awaiting review, and published only once approved.'),
        ];
    }
}
