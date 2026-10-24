{{--
    Where everybody stands: invited, registered with a profile in progress,
    and ready. One page, so nobody is lost between invitation and Directory.
--}}
<x-app-layout>
    <div class="mx-auto max-w-4xl">
        <h1 class="text-2xl font-semibold text-stone-800">Collaborators</h1>
        <p class="mt-1 text-sm text-stone-600">
            Everyone you have invited, from invitation to the Collaborator Directory. You are also
            emailed each time somebody registers.
        </p>

        <div class="mt-6 grid gap-3 sm:grid-cols-3">
            <div class="rounded-lg border border-stone-200 bg-white p-4">
                <p class="text-2xl font-semibold text-stone-800">{{ $waiting->count() }}</p>
                <p class="text-sm text-stone-600">Invited, not yet registered</p>
            </div>
            <div class="rounded-lg border border-stone-200 bg-white p-4">
                <p class="text-2xl font-semibold text-stone-800">{{ $inProgress->count() }}</p>
                <p class="text-sm text-stone-600">Registered, profile in progress</p>
            </div>
            <div class="rounded-lg border border-stone-200 bg-white p-4">
                <p class="text-2xl font-semibold text-stone-800">{{ $ready->count() }}</p>
                <p class="text-sm text-stone-600">Profile ready for the Directory</p>
            </div>
        </div>

        {{-- ------------------------------------------------------------- --}}
        <section class="mt-10">
            <h2 class="text-lg font-medium text-stone-800">Invited, not yet registered</h2>
            @if ($waiting->isEmpty())
                <p class="mt-2 text-sm text-stone-500">Nobody is waiting on an invitation.</p>
            @else
                <ul class="mt-3 divide-y divide-stone-100 rounded-lg border border-stone-200 bg-white">
                    @foreach ($waiting as $invitation)
                        @php $expired = $invitation->status === \App\Enums\InvitationStatus::Expired || $invitation->expires_at?->isPast(); @endphp
                        <li class="flex flex-wrap items-center justify-between gap-3 p-4">
                            <div>
                                <p class="text-sm font-medium text-stone-800">{{ trim($invitation->first_name.' '.$invitation->last_name) }}</p>
                                <p class="text-xs text-stone-500">
                                    Invited {{ $invitation->created_at?->format('F j, Y') }}
                                    @if ($expired)
                                        · <span class="text-amber-700">invitation expired {{ $invitation->expires_at?->format('F j, Y') }}</span>
                                    @else
                                        · link open until {{ $invitation->expires_at?->format('F j, Y') }}
                                    @endif
                                </p>
                            </div>
                            <form method="POST" action="{{ route('admin.invitations.reissue', $invitation) }}">
                                @csrf
                                <button type="submit" class="rounded-md border border-stone-300 px-3 py-1.5 text-xs font-medium hover:bg-stone-50">
                                    {{ $expired ? 'Issue a fresh link' : 'Replace the link' }}
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        {{-- ------------------------------------------------------------- --}}
        <section class="mt-10">
            <h2 class="text-lg font-medium text-stone-800">Registered, profile in progress</h2>
            @if ($inProgress->isEmpty())
                <p class="mt-2 text-sm text-stone-500">Nobody is part way through a profile.</p>
            @else
                <ul class="mt-3 divide-y divide-stone-100 rounded-lg border border-stone-200 bg-white">
                    @foreach ($inProgress as $row)
                        <li class="p-4">
                            <p class="text-sm font-medium text-stone-800">{{ $row['collaborator']->user?->fullName() }}</p>
                            <p class="text-xs text-stone-500">
                                Registered {{ $row['collaborator']->admitted_at?->format('F j, Y') }}
                                @if ($row['missing'] !== [])
                                    · still needs: {{ collect($row['missing'])->pluck('label')->join(', ') }}
                                @endif
                            </p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        {{-- ------------------------------------------------------------- --}}
        <section class="mt-10">
            <h2 class="text-lg font-medium text-stone-800">Profile ready for the Directory</h2>
            @if ($ready->isEmpty())
                <p class="mt-2 text-sm text-stone-500">No profile is ready yet.</p>
            @else
                <ul class="mt-3 divide-y divide-stone-100 rounded-lg border border-stone-200 bg-white">
                    @foreach ($ready as $row)
                        <li class="flex flex-wrap items-center justify-between gap-3 p-4">
                            <div>
                                <p class="text-sm font-medium text-stone-800">{{ $row['collaborator']->user?->fullName() }}</p>
                                <p class="text-xs text-stone-500">
                                    Ready since {{ $row['profile']?->completed_at?->format('F j, Y') }}
                                    · {{ $row['stage'] === 'listed' ? 'appears in the Directory' : 'withheld from the Directory' }}
                                </p>
                            </div>
                            @if ($row['stage'] === 'listed' && $row['profile'])
                                <a href="{{ route('profile.show', $row['profile']) }}" class="text-xs underline underline-offset-2">View profile</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</x-app-layout>
