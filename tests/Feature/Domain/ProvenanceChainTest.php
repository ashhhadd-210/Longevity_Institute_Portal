<?php

namespace Tests\Feature\Domain;

use App\Enums\AdmissionSubjectType;
use App\Enums\ApplicationStatus;
use App\Models\AdmissionDecision;
use App\Models\Application;
use App\Models\Collaborator;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Years later, the Institute must be able to answer "how was this collaborator
 * admitted?" entirely from foreign keys:
 * Collaborator → Invitation → AdmissionDecision → Application (doc 02 §2).
 */
class ProvenanceChainTest extends TestCase
{
    use RefreshDatabase;

    public function test_admission_provenance_is_traversable_from_collaborator_to_application(): void
    {
        $application = Application::factory()->create([
            'status' => ApplicationStatus::Approved->value,
        ]);

        $decision = AdmissionDecision::factory()->create([
            'subject_type' => AdmissionSubjectType::Application->value,
            'subject_id' => $application->id,
        ]);

        $invitation = Invitation::factory()->accepted()->create([
            'admission_decision_id' => $decision->id,
            'email' => $application->email,
        ]);

        $collaborator = Collaborator::factory()->create([
            'invitation_id' => $invitation->id,
        ]);

        $origin = $collaborator->invitation->admissionDecision->subject;

        $this->assertInstanceOf(Application::class, $origin);
        $this->assertTrue($origin->is($application));
        $this->assertSame(
            AdmissionSubjectType::Application,
            $collaborator->invitation->admissionDecision->subjectType()
        );
    }

    public function test_direct_invitation_provenance_has_a_decision_and_no_subject(): void
    {
        $invitation = Invitation::factory()->accepted()->create();
        $collaborator = Collaborator::factory()->create(['invitation_id' => $invitation->id]);

        $decision = $collaborator->invitation->admissionDecision;

        $this->assertSame(AdmissionSubjectType::Direct, $decision->subjectType());
        $this->assertNull($decision->subject);
    }
}
