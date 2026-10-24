<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\InvitationStatus;
use App\Enums\ReferralStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Collaborator;
use App\Models\Invitation;
use App\Models\Referral;
use Illuminate\Contracts\View\View;

/**
 * Minimal administrative shell for the Foundation demo. The operational
 * surfaces (review queue, invitation management) arrive in Milestone 2 on top
 * of the same domain.
 */
class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'collaboratorCount' => Collaborator::query()->count(),
            'openApplications' => Application::query()
                ->whereIn('status', [ApplicationStatus::Submitted->value, ApplicationStatus::UnderReview->value])
                ->count(),
            'openReferrals' => Referral::query()
                ->whereIn('status', [ReferralStatus::Submitted->value, ReferralStatus::UnderReview->value])
                ->count(),
            'outstandingInvitations' => Invitation::query()
                ->whereIn('status', [InvitationStatus::Issued->value, InvitationStatus::Sent->value])
                ->count(),
        ]);
    }
}
