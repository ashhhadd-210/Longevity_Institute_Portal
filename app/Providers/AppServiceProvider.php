<?php

namespace App\Providers;

use App\Models\AdmissionDecision;
use App\Models\Application;
use App\Models\Collaborator;
use App\Models\Consent;
use App\Models\DataRequest;
use App\Models\EmailMessage;
use App\Models\ExpertiseArea;
use App\Models\Invitation;
use App\Models\Organisation;
use App\Models\Profile;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Permission bridge (ADR-004): any dotted ability name is resolved as
        // a data-driven permission check. Policies use undotted method names
        // and are unaffected. Application code never checks role names.
        Gate::before(function (User $user, string $ability): ?bool {
            return str_contains($ability, '.') ? $user->hasPermission($ability) : null;
        });

        // Stable, storage-safe morph identifiers. Polymorphic columns
        // (admission_decisions.subject_type, audit_events.subject_type,
        // email_messages.related_type) must never contain PHP class names,
        // which would couple institutional records to code layout.
        Relation::enforceMorphMap([
            'user' => User::class,
            'collaborator' => Collaborator::class,
            'profile' => Profile::class,
            'application' => Application::class,
            'referral' => Referral::class,
            'admission_decision' => AdmissionDecision::class,
            'invitation' => Invitation::class,
            'organisation' => Organisation::class,
            'expertise_area' => ExpertiseArea::class,
            'consent' => Consent::class,
            'data_request' => DataRequest::class,
            'email_message' => EmailMessage::class,
        ]);
    }
}
