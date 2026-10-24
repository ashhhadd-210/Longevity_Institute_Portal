<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CollaborationInterest;
use App\Models\FeedbackNote;
use Illuminate\View\View;

/**
 * What the community is telling the Institute.
 *
 * Two lists the Founder reads by eye at Alpha: interests one collaborator has
 * registered in another, which the Institute passes on, and feedback about the
 * Portal itself. Both are the raw material for what gets built next.
 */
class CommunityController extends Controller
{
    public function interests(): View
    {
        return view('admin.community.interests', [
            'interests' => CollaborationInterest::query()
                ->with(['from.collaborator.user', 'to.collaborator.user'])
                ->latest()
                ->paginate(50),
        ]);
    }

    public function feedback(): View
    {
        return view('admin.community.feedback', [
            'notes' => FeedbackNote::query()->with('user')->latest()->paginate(50),
        ]);
    }
}
