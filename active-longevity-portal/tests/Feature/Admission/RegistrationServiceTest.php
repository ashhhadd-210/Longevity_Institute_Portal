<?php

namespace Tests\Feature\Admission;

use App\Enums\InvitationStatus;
use App\Events\CollaboratorAdmitted;
use App\Models\AuditEvent;
use App\Models\Invitation;
use App\Services\Admission\RegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use Tests\Concerns\CreatesUsersWithRoles;
use Tests\TestCase;

class RegistrationServiceTest extends TestCase
{
    use CreatesUsersWithRoles;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    /**
     * @return array{first_name: string, last_name: string, password: string, privacy_consent: bool, ip_address: string}
     */
    private function validData(): array
    {
        return [
            'first_name' => 'Søren',
            'last_name' => 'Kierkegård',
            'password' => 'a-long-and-proper-password',
            'privacy_consent' => true,
            'ip_address' => '203.0.113.10',
        ];
    }

    public function test_registration_creates_the_admitted_collaborator_atomically(): void
    {
        Event::fake([CollaboratorAdmitted::class]);
        $invitation = Invitation::factory()->create(['email' => 'soren@example.org']);

        $collaborator = app(RegistrationService::class)->register($invitation, $this->validData());

        $user = $collaborator->user;
        $this->assertSame('soren@example.org', $user->email);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue(Hash::check('a-long-and-proper-password', $user->password));
        $this->assertTrue($user->hasRole('collaborator'));
        $this->assertFalse($user->hasPermission('platform.admin.access'));

        $this->assertSame(InvitationStatus::Accepted, $invitation->fresh()->status);
        $this->assertTrue($collaborator->invitation->is($invitation));
        // Listed from the start: the profile, not a switch, decides when they appear.
        $this->assertTrue($collaborator->fresh()->directory_visible);
        $this->assertNotNull($collaborator->profile);
        $this->assertFalse($collaborator->isDirectoryListed());

        $consent = $user->consents()->first();
        $this->assertNotNull($consent);
        $this->assertSame(config('portal.privacy_notice_version'), $consent->version);
        $this->assertSame('203.0.113.10', $consent->ip_address);

        $this->assertTrue(
            AuditEvent::query()->where('event_type', 'admission.collaborator.admitted')->exists()
        );
        Event::assertDispatched(CollaboratorAdmitted::class);
    }

    public function test_an_invitation_cannot_be_used_twice(): void
    {
        Event::fake([CollaboratorAdmitted::class]);
        $invitation = Invitation::factory()->create();

        app(RegistrationService::class)->register($invitation, $this->validData());

        $this->expectException(InvalidArgumentException::class);

        app(RegistrationService::class)->register($invitation->fresh(), $this->validData());
    }

    public function test_an_expired_invitation_is_rejected(): void
    {
        $invitation = Invitation::factory()->create(['expires_at' => now()->subDay()]);

        $this->expectException(InvalidArgumentException::class);

        app(RegistrationService::class)->register($invitation, $this->validData());
    }

    public function test_registration_requires_privacy_consent(): void
    {
        $invitation = Invitation::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        app(RegistrationService::class)->register(
            $invitation,
            array_replace($this->validData(), ['privacy_consent' => false])
        );
    }
}
