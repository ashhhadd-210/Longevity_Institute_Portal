<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use App\Enums\DecisionOutcome;
use App\Enums\InvitationStatus;
use App\Enums\ReferralStatus;
use App\Models\AdmissionDecision;
use App\Models\Application;
use App\Models\Collaborator;
use App\Models\ExpertiseArea;
use App\Models\Invitation;
use App\Models\Organisation;
use App\Models\Profile;
use App\Models\Referral;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Curated demonstration data for local development and staging reviews.
 *
 * Deliberately fixed rather than randomly generated: the Institute sees this
 * data during milestone reviews, so it should read like a plausible community
 * and stay identical between runs. It also avoids any dependency on
 * development-only packages, which deployed environments do not install.
 *
 * Every record here is fictional. Production is never seeded with fixtures.
 */
class DemoDataSeeder extends Seeder
{
    /** @var list<array<string, mixed>> */
    private array $collaborators = [
        [
            'first' => 'Søren', 'middle' => null, 'last' => 'Kierkegård',
            'credentials' => ['MD', 'PhD'],
            'title' => 'Professor of Geriatric Medicine',
            'org' => 'University of Copenhagen', 'country' => 'DK', 'city' => 'Copenhagen',
            'summary' => 'Geriatrician studying how multimorbidity changes what "healthy" means after eighty.',
            'expertise' => ['medicine', 'gerontology'],
        ],
        [
            'first' => 'José', 'middle' => 'María', 'last' => 'Ñíguez',
            'credentials' => ['PhD'],
            'title' => 'Director, Center for Exercise Physiology',
            'org' => 'Universidad de Granada', 'country' => 'ES', 'city' => 'Granada',
            'summary' => 'Exercise physiologist working on strength retention in the eighth and ninth decades.',
            'expertise' => ['exercise-science', 'public-health'],
        ],
        [
            'first' => 'Amara', 'middle' => null, 'last' => 'Okonkwo',
            'credentials' => ['MPH', 'DrPH'],
            'title' => 'Professor of Public Health Policy',
            'org' => 'University of Ibadan', 'country' => 'NG', 'city' => 'Ibadan',
            'summary' => 'Health policy researcher on aging in rapidly urbanizing populations.',
            'expertise' => ['health-policy', 'public-health'],
        ],
        [
            'first' => 'Zoë', 'middle' => null, 'last' => 'Åkerström',
            'credentials' => ['PhD'],
            'title' => 'Principal Investigator, Cognitive Aging Laboratory',
            'org' => 'Karolinska Institutet', 'country' => 'SE', 'city' => 'Stockholm',
            'summary' => 'Neuroscientist examining cognitive reserve and lifelong learning.',
            'expertise' => ['neuroscience', 'psychology'],
        ],
        [
            'first' => 'Hiroshi', 'middle' => null, 'last' => 'Tanaka',
            'credentials' => ['PhD', 'RD'],
            'title' => 'Professor of Nutrition Science',
            'org' => 'Kyoto University', 'country' => 'JP', 'city' => 'Kyoto',
            'summary' => 'Nutrition scientist studying dietary patterns in long-lived communities.',
            'expertise' => ['nutrition-science', 'medicine'],
        ],
        [
            'first' => 'Margaret', 'middle' => 'Anne', 'last' => 'Whitfield',
            'credentials' => ['MArch'],
            'title' => 'Professor of Urban Design',
            'org' => 'University of Toronto', 'country' => 'CA', 'city' => 'Toronto',
            'summary' => 'Architect researching neighbourhood design that keeps older adults mobile and connected.',
            'expertise' => ['architecture-urban-design', 'public-health'],
        ],
    ];

    public function run(): void
    {
        $collaboratorRoleId = Role::query()->where('name', 'collaborator')->value('id');
        $password = Hash::make('demo-password-not-for-production');

        $founder = User::query()
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['founder', 'administrator']))
            ->first();

        // On a freshly created database there is no administrator yet, so make
        // one: `migrate:fresh --seed` should leave a developer with an
        // environment they can actually sign in to.
        if ($founder === null) {
            $founder = User::query()->create([
                'first_name' => 'Demo',
                'last_name' => 'Administrator',
                'email' => 'demo.administrator@example.test',
                'password' => $password,
            ]);
            $founder->forceFill(['email_verified_at' => now()])->save();
            $founder->roleAssignments()->create([
                'role_id' => Role::query()->where('name', 'founder')->value('id'),
                'assigned_at' => now(),
            ]);
        }

        foreach ($this->collaborators as $index => $person) {
            $email = 'demo.'.strtolower($person['last']).'@example.test';

            if (User::query()->where('email', $email)->exists()) {
                continue;
            }

            $user = User::query()->create([
                'first_name' => $person['first'],
                'middle_name' => $person['middle'],
                'last_name' => $person['last'],
                'email' => $email,
                'password' => $password,
            ]);
            $user->forceFill(['email_verified_at' => now()])->save();
            $user->roleAssignments()->create([
                'role_id' => $collaboratorRoleId,
                'assigned_at' => now(),
            ]);

            // Full provenance, exactly as a real admission would record it.
            $decision = AdmissionDecision::query()->create([
                'subject_type' => null,
                'subject_id' => null,
                'decision' => DecisionOutcome::Approved->value,
                'rationale' => 'Founding collaborator, invited directly by the Institute.',
                'decided_by' => $founder->id,
                'decided_at' => now()->subDays(30 - $index),
            ]);

            $invitation = Invitation::query()->create([
                'email' => $email,
                'first_name' => $person['first'],
                'last_name' => $person['last'],
                'token_hash' => hash('sha256', 'demo-consumed-token-'.$index),
                'expires_at' => now()->subDays(16 - $index),
                'status' => InvitationStatus::Accepted->value,
                'admission_decision_id' => $decision->id,
                'issued_by' => $founder->id,
                'send_count' => 1,
                'last_sent_at' => now()->subDays(30 - $index),
                'accepted_at' => now()->subDays(28 - $index),
            ]);

            $collaborator = Collaborator::query()->create([
                'user_id' => $user->id,
                'invitation_id' => $invitation->id,
                'cohort' => 'wave_0',
                'directory_visible' => true,
                'admitted_at' => now()->subDays(28 - $index),
            ]);

            $organisation = Organisation::query()->firstOrCreate(
                ['name' => $person['org']],
                ['country_code' => $person['country']]
            );

            Profile::query()->create([
                'collaborator_id' => $collaborator->id,
                'professional_title' => $person['title'],
                'one_line_summary' => $person['summary'],
                'about' => $person['summary'].' Member of the Institute\'s founding cohort of collaborators.',
                'credentials' => $person['credentials'],
                'country_code' => $person['country'],
                'city' => $person['city'],
                'languages' => ['English'],
                'organisation_id' => $organisation->id,
                'completed_at' => now()->subDays(20 - $index),
            ]);

            $collaborator->expertiseAreas()->sync(
                ExpertiseArea::query()->whereIn('slug', $person['expertise'])->pluck('id')
            );
        }

        // Two applications and one referral awaiting review, so the
        // administrative view shows a realistic queue.
        Application::query()->firstOrCreate(
            ['email' => 'demo.applicant.reyes@example.test'],
            [
                'first_name' => 'Carmen', 'last_name' => 'Reyes',
                'organisation_text' => 'Instituto Nacional de Salud Pública',
                'professional_title' => 'Senior Research Fellow, Epidemiology',
                'country_code' => 'MX',
                'expertise_claimed' => ['Public Health'],
                'motivation' => 'I would value working across disciplines on aging in Latin American populations.',
                'status' => ApplicationStatus::Submitted->value,
                'submitted_at' => now()->subDays(3),
            ]
        );

        Application::query()->firstOrCreate(
            ['email' => 'demo.applicant.novak@example.test'],
            [
                'first_name' => 'Petr', 'last_name' => 'Novák',
                'organisation_text' => 'Charles University',
                'professional_title' => 'Lecturer in Health Economics',
                'country_code' => 'CZ',
                'expertise_claimed' => ['Economics'],
                'motivation' => 'My work concerns the economics of extended working lives.',
                'status' => ApplicationStatus::UnderReview->value,
                'submitted_at' => now()->subDays(6),
            ]
        );

        $referrer = Collaborator::query()->first();

        if ($referrer !== null) {
            Referral::query()->firstOrCreate(
                ['candidate_email' => 'demo.referred.lindqvist@example.test'],
                [
                    'referrer_collaborator_id' => $referrer->id,
                    'candidate_first_name' => 'Elin',
                    'candidate_last_name' => 'Lindqvist',
                    'candidate_organisation' => 'Uppsala University',
                    'candidate_title' => 'Associate Professor of Sociology',
                    'candidate_country_code' => 'SE',
                    'candidate_expertise' => ['Sociology'],
                    'reason' => 'Elin\'s work on social isolation would complement the Institute\'s existing strengths.',
                    'relationship_to_referrer' => 'Former research collaborator',
                    'status' => ReferralStatus::Submitted->value,
                ]
            );
        }
    }
}
