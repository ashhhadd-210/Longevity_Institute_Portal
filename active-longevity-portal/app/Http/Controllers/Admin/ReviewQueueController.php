<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Referral;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Everything waiting for the Institute's attention, in one list.
 *
 * Cases that have been set aside remain here rather than being filed away
 * somewhere quieter. A pause is not a disposal, and a queue that hides paused
 * work is a queue that loses it.
 */
class ReviewQueueController extends Controller
{
    public function index(Request $request): View
    {
        $show = $request->string('show', 'open')->toString();

        $applications = Application::query()
            ->with(['candidateCase', 'informationRequests'])
            ->when($show === 'open', fn ($query) => $query->whereIn('status', $this->openStatuses()))
            ->when($show === 'decided', fn ($query) => $query->whereIn('status', ['approved', 'declined']))
            ->latest('submitted_at')
            ->get();

        $referrals = Referral::query()
            ->with(['candidateCase', 'referrer.user', 'relationship', 'informationRequests'])
            ->when($show === 'open', fn ($query) => $query->whereIn('status', $this->openStatuses()))
            ->when($show === 'decided', fn ($query) => $query->whereIn('status', ['approved', 'declined']))
            ->latest('created_at')
            ->get();

        return view('admin.queue.index', [
            'show' => $show,
            'items' => $this->merge($applications, $referrals),
            'openCount' => Application::query()->whereIn('status', $this->openStatuses())->count()
                + Referral::query()->whereIn('status', $this->openStatuses())->count(),
        ]);
    }

    /**
     * @return list<string>
     */
    private function openStatuses(): array
    {
        return ['submitted', 'under_review', 'awaiting_information', 'set_aside'];
    }

    /**
     * One list, oldest first, so that nobody waits longest simply because they
     * arrived by a different route.
     *
     * @param  Collection<int, Application>  $applications
     * @param  Collection<int, Referral>  $referrals
     * @return Collection<int, array{subject: Application|Referral, kind: string, name: string, arrived: Carbon}>
     */
    private function merge(Collection $applications, Collection $referrals): Collection
    {
        $rows = $applications->map(fn (Application $application): array => [
            'subject' => $application,
            'kind' => 'application',
            'name' => $application->fullName(),
            'arrived' => $application->submitted_at,
        ])->concat($referrals->map(fn (Referral $referral): array => [
            'subject' => $referral,
            'kind' => 'referral',
            'name' => $referral->candidateFullName(),
            'arrived' => $referral->created_at,
        ]));

        return $rows->sortBy(fn (array $row): string => $row['arrived']->toIso8601String())->values();
    }
}
