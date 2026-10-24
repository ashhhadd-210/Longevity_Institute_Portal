{{--
    One person: how they arrived, what has happened, and what may be done next.

    Where somebody reached the Institute twice, both submissions are shown
    because each carries something the other lacks, but there is one history and
    one set of actions, because there is one person and one judgment to make.
--}}
<x-app-layout>
    <div class="mx-auto max-w-4xl">
        <p class="text-sm">
            <a href="{{ route('admin.queue.index') }}" class="text-stone-500 underline underline-offset-2">Back to the queue</a>
        </p>

        <h1 class="mt-3 text-xl font-semibold tracking-tight">{{ $case->display_name }}</h1>
        @if ($case->email)
            <p class="mt-1 text-sm text-stone-500">{{ $case->email }}</p>
        @endif

        @if (session('status'))
            <div class="mt-6 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        <x-form-errors class="mt-6" />

        {{-- Possible duplicates. Suggested, never merged automatically. --}}
        @if ($duplicates->isNotEmpty())
            <section class="mt-6 rounded-md border border-amber-200 bg-amber-50 p-4">
                <h2 class="text-sm font-medium text-amber-900">This may be the same person as another record</h2>
                <div class="mt-3 space-y-3">
                    @foreach ($duplicates as $match)
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-md bg-white px-3 py-2">
                            <div class="text-sm">
                                <a href="{{ route('admin.cases.show', $match->case) }}"
                                   class="font-medium underline underline-offset-2">{{ $match->case->display_name }}</a>
                                <span class="text-stone-500">, {{ $match->confidence->label() }}</span>
                                <p class="mt-0.5 text-xs text-stone-500">{{ $match->confidence->explanation() }}</p>
                            </div>
                            @if ($match->isJoinable())
                                <form method="POST" action="{{ route('admin.cases.join', $case) }}">
                                    @csrf
                                    <input type="hidden" name="absorbed_case_id" value="{{ $match->case->id }}">
                                    <button type="submit" class="rounded-md border border-stone-300 px-3 py-1.5 text-xs font-medium hover:bg-stone-50">
                                        These are the same person
                                    </button>
                                </form>
                            @else
                                {{-- Already judged, so joining would be refused. Say
                                     why rather than offer an action that will fail. --}}
                                <p class="max-w-xs text-xs text-stone-600">
                                    {{ $match->settled }}
                                    If they are the same person, work from that case rather than this one.
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- What arrived, in full. --}}
        <section class="mt-8">
            <h2 class="text-sm font-medium uppercase tracking-wide text-stone-500">Submissions</h2>

            @foreach ($case->applications as $application)
                <article class="mt-3 rounded-md border border-stone-200 bg-white p-5">
                    <div class="flex items-baseline justify-between">
                        <h3 class="font-medium">Application through the website</h3>
                        <span class="text-xs text-stone-500">{{ $application->submitted_at->format('j F Y') }}</span>
                    </div>
                    <dl class="mt-3 grid gap-x-6 gap-y-2 text-sm sm:grid-cols-2">
                        <div><dt class="text-stone-500">Name</dt><dd>{{ $application->fullName() }}</dd></div>
                        <div><dt class="text-stone-500">Email</dt><dd>{{ $application->email }}</dd></div>
                        @if ($application->professional_title)
                            {{-- Shown in full and wrapped. Never truncated. --}}
                            <div class="sm:col-span-2"><dt class="text-stone-500">Title</dt><dd class="break-words">{{ $application->professional_title }}</dd></div>
                        @endif
                        @if ($application->organisation_text)
                            <div><dt class="text-stone-500">Organization</dt><dd class="break-words">{{ $application->organisation_text }}</dd></div>
                        @endif
                        @if ($application->country_code)
                            <div><dt class="text-stone-500">Country</dt><dd>{{ $application->country_code }}</dd></div>
                        @endif
                    </dl>
                    @if ($application->motivation)
                        <p class="mt-4 whitespace-pre-line border-t border-stone-100 pt-4 text-sm leading-relaxed text-stone-700">{{ $application->motivation }}</p>
                    @endif
                </article>
            @endforeach

            @foreach ($case->referrals as $referral)
                <article class="mt-3 rounded-md border border-stone-200 bg-white p-5">
                    <div class="flex items-baseline justify-between">
                        <h3 class="font-medium">Referred by {{ $referral->referrer?->user?->fullName() ?? 'a collaborator' }}</h3>
                        <span class="text-xs text-stone-500">{{ $referral->created_at->format('j F Y') }}</span>
                    </div>
                    <dl class="mt-3 grid gap-x-6 gap-y-2 text-sm sm:grid-cols-2">
                        <div><dt class="text-stone-500">Name</dt><dd>{{ $referral->candidateFullName() }}</dd></div>
                        <div><dt class="text-stone-500">Email</dt><dd>{{ $referral->candidate_email }}</dd></div>
                        @if ($referral->candidate_title)
                            <div class="sm:col-span-2"><dt class="text-stone-500">Title</dt><dd class="break-words">{{ $referral->candidate_title }}</dd></div>
                        @endif
                        @if ($referral->candidate_organisation)
                            <div><dt class="text-stone-500">Organization</dt><dd class="break-words">{{ $referral->candidate_organisation }}</dd></div>
                        @endif
                    </dl>

                    @if ($referral->reason)
                        <p class="mt-4 whitespace-pre-line border-t border-stone-100 pt-4 text-sm leading-relaxed text-stone-700">{{ $referral->reason }}</p>
                    @endif

                    {{-- Institute-only, permanently. Never shown to the
                         candidate, never auto-inserted into an invitation,
                         never on a profile. --}}
                    @if ($referral->relationshipLabel() || $referral->acquaintance_note)
                        <div class="mt-4 rounded-md bg-stone-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-stone-500">
                                Seen by the Institute only
                            </p>
                            @if ($referral->relationshipLabel())
                                <p class="mt-2 text-sm"><span class="text-stone-500">Relationship:</span> {{ $referral->relationshipLabel() }}</p>
                            @endif
                            @if ($referral->acquaintance_note)
                                <p class="mt-1 whitespace-pre-line text-sm text-stone-700">{{ $referral->acquaintance_note }}</p>
                            @endif
                        </div>
                    @endif
                </article>
            @endforeach
        </section>

        {{-- The four actions, on whichever submissions are still open. --}}
        @foreach ($openSubjects as $subject)
            @php($type = $subject instanceof \App\Models\Application ? 'application' : 'referral')
            @php($recipient = $type === 'referral' ? 'the referrer' : 'the applicant')

            <section class="mt-8 rounded-md border border-stone-200 bg-white p-5">
                <h2 class="text-sm font-medium">
                    Decide on the {{ $type }}
                    <span class="ml-2 rounded-full bg-stone-100 px-2 py-0.5 text-xs font-normal text-stone-600">
                        {{ $subject->status->label() }}
                    </span>
                </h2>

                @if ($subject->status === \App\Enums\ApplicationStatus::SetAside || $subject->status === \App\Enums\ReferralStatus::SetAside)
                    <div class="mt-4 rounded-md bg-stone-50 p-4 text-sm">
                        <p class="text-stone-700">
                            Set aside{{ $subject->set_aside_at ? ' on '.$subject->set_aside_at->format('j F Y') : '' }}.
                            Nothing was sent to anybody.
                        </p>
                        @if ($subject->set_aside_reason)
                            <p class="mt-1 text-stone-600">{{ $subject->set_aside_reason }}</p>
                        @endif
                        <form method="POST" action="{{ route('admin.review.resume', [$type, $subject->id]) }}" class="mt-3">
                            @csrf
                            <button type="submit" class="rounded-md border border-stone-300 px-3 py-1.5 text-xs font-medium hover:bg-stone-100">
                                Return to consideration
                            </button>
                        </form>
                    </div>
                @else
                    <div class="mt-5 space-y-6">
                        {{-- 1. Approve --}}
                        <form method="POST" action="{{ route('admin.review.approve', [$type, $subject->id]) }}"
                              class="border-b border-stone-100 pb-6">
                            @csrf
                            <label class="block text-sm font-medium">Approve</label>
                            <p class="mt-1 text-xs text-stone-500">
                                A final judgment, recorded permanently. It does not invite anybody:
                                you issue the invitation separately, when you are ready.
                            </p>
                            <textarea name="rationale" rows="2" placeholder="Any note for the record (optional)"
                                      class="mt-2 w-full rounded-md border-stone-300 text-sm shadow-sm"></textarea>
                            <button type="submit" class="mt-2 rounded-md bg-stone-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-stone-700">
                                Approve
                            </button>
                        </form>

                        {{-- 2. Need more information --}}
                        <form method="POST" action="{{ route('admin.review.information', [$type, $subject->id]) }}"
                              class="border-b border-stone-100 pb-6">
                            @csrf
                            <label class="block text-sm font-medium">Need more information</label>
                            <p class="mt-1 text-xs text-stone-500">
                                A question, not a decision. The case stays open and you may ask again.
                                This goes to {{ $recipient }}.
                                @if ($type === 'referral')
                                    The person they recommended is not contacted.
                                @endif
                            </p>
                            <textarea name="required" rows="3" required placeholder="What would you like to know?"
                                      class="mt-2 w-full rounded-md border-stone-300 text-sm shadow-sm"></textarea>
                            <button type="submit" class="mt-2 rounded-md border border-stone-300 px-3 py-1.5 text-sm font-medium hover:bg-stone-50">
                                Send the question
                            </button>
                        </form>

                        {{-- 3. Set aside --}}
                        <form method="POST" action="{{ route('admin.review.set-aside', [$type, $subject->id]) }}"
                              class="border-b border-stone-100 pb-6">
                            @csrf
                            <label class="block text-sm font-medium">Set aside</label>
                            <p class="mt-1 text-xs text-stone-500">
                                A pause, for your own use. Nothing is sent, nothing is decided, and this
                                stays in the queue where you will see it.
                            </p>
                            <textarea name="reason" rows="2" placeholder="Why, for your own memory later (optional)"
                                      class="mt-2 w-full rounded-md border-stone-300 text-sm shadow-sm"></textarea>
                            <button type="submit" class="mt-2 rounded-md border border-stone-300 px-3 py-1.5 text-sm font-medium hover:bg-stone-50">
                                Set aside
                            </button>
                        </form>

                        {{-- 4. Decline --}}
                        <form method="POST" action="{{ route('admin.review.decline', [$type, $subject->id]) }}">
                            @csrf
                            <label class="block text-sm font-medium">Decline</label>
                            <p class="mt-1 text-xs text-stone-500">
                                A final judgment, recorded with your reasoning. Nothing is sent.
                                @if ($type === 'referral')
                                    The referrer is not told.
                                @endif
                            </p>
                            <textarea name="rationale" rows="2" required placeholder="Your reasoning, for the record"
                                      class="mt-2 w-full rounded-md border-stone-300 text-sm shadow-sm"></textarea>
                            <button type="submit" class="mt-2 rounded-md border border-stone-300 px-3 py-1.5 text-sm font-medium text-stone-700 hover:bg-stone-50">
                                Decline
                            </button>
                        </form>
                    </div>
                @endif

                {{-- Outstanding questions and their replies. --}}
                @foreach ($subject->informationRequests as $informationRequest)
                    <div class="mt-6 rounded-md bg-stone-50 p-4 text-sm">
                        <p class="text-xs uppercase tracking-wide text-stone-500">
                            Asked {{ $informationRequest->requested_at->format('j F Y') }}
                        </p>
                        <p class="mt-1 whitespace-pre-line text-stone-700">{{ $informationRequest->required }}</p>

                        @if ($informationRequest->responded_at)
                            <p class="mt-3 border-t border-stone-200 pt-3 text-xs uppercase tracking-wide text-stone-500">Reply</p>
                            <p class="mt-1 whitespace-pre-line text-stone-700">{{ $informationRequest->response }}</p>
                        @else
                            <form method="POST" action="{{ route('admin.information.response', $informationRequest) }}" class="mt-3">
                                @csrf
                                <textarea name="response" rows="2" required placeholder="Record the reply you received"
                                          class="w-full rounded-md border-stone-300 text-sm shadow-sm"></textarea>
                                <button type="submit" class="mt-2 rounded-md border border-stone-300 px-3 py-1.5 text-xs font-medium hover:bg-white">
                                    Record the reply
                                </button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </section>
        @endforeach

        {{-- Invitation: a consequence of approval, never a review action. --}}
        @if ($blockingInvitation)
            <section class="mt-8 rounded-md border border-stone-200 bg-white p-5">
                <h2 class="text-sm font-medium">Invitation</h2>
                <p class="mt-2 text-sm text-stone-600">
                    @if ($blockingInvitation->status === \App\Enums\InvitationStatus::Accepted)
                        This person has registered and is an admitted collaborator.
                    @else
                        An invitation is outstanding, issued {{ $blockingInvitation->created_at->format('j F Y') }}
                        by {{ $blockingInvitation->delivery_channel->label() }}.
                        It lapses on {{ $blockingInvitation->expires_at->format('j F Y') }}.
                    @endif
                </p>

                @if ($blockingInvitation->status !== \App\Enums\InvitationStatus::Accepted)
                    <div class="mt-4 flex flex-wrap gap-3">
                        <form method="POST" action="{{ route('admin.invitations.reissue', $blockingInvitation) }}">
                            @csrf
                            <button type="submit" class="rounded-md border border-stone-300 px-3 py-1.5 text-sm font-medium hover:bg-stone-50">
                                Issue a fresh link
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.invitations.revoke', $blockingInvitation) }}">
                            @csrf
                            <button type="submit" class="rounded-md border border-stone-300 px-3 py-1.5 text-sm font-medium text-stone-700 hover:bg-stone-50">
                                Withdraw the invitation
                            </button>
                        </form>
                    </div>
                    <p class="mt-2 text-xs text-stone-500">
                        A fresh link retires the previous one immediately.
                    </p>
                @endif
            </section>
        @elseif ($approvedSubject)
            <section class="mt-8 rounded-md border border-stone-200 bg-white p-5">
                <h2 class="text-sm font-medium">Invitation</h2>
                <p class="mt-2 text-sm text-stone-600">
                    This person has been approved. You can invite them whenever you are ready.
                </p>
                <a href="{{ route('admin.invitations.create-for-case', $case) }}"
                   class="mt-4 inline-block rounded-md bg-stone-800 px-3 py-2 text-sm font-medium text-white hover:bg-stone-700">
                    Generate invitation
                </a>
            </section>
        @endif

        {{-- The assembled history. --}}
        <section class="mt-8">
            <h2 class="text-sm font-medium uppercase tracking-wide text-stone-500">History</h2>
            <ol class="mt-3 space-y-4 border-l border-stone-200 pl-5">
                @foreach ($history as $entry)
                    <li class="relative">
                        <span class="absolute -left-[1.4rem] top-1.5 h-2 w-2 rounded-full {{ $entry->isDecision ? 'bg-stone-800' : 'bg-stone-300' }}"></span>
                        <p class="text-sm {{ $entry->isDecision ? 'font-medium text-stone-800' : 'text-stone-700' }}">
                            {{ $entry->summary }}
                        </p>
                        <p class="text-xs text-stone-500">
                            {{ $entry->at->format('j F Y, H:i') }}@if ($entry->actor) · {{ $entry->actor }}@endif
                        </p>
                        @if ($entry->detail)
                            <p class="mt-1 whitespace-pre-line text-sm text-stone-600">{{ $entry->detail }}</p>
                        @endif
                    </li>
                @endforeach
            </ol>
        </section>
    </div>
</x-app-layout>
