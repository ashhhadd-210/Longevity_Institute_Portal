{{--
    The one moment the usable link exists.

    Only its hash is stored, so there is no page anywhere that can show it
    again, including this one on a refresh. That is the point: a link nobody can
    look up later is a link nobody can steal later.
--}}
<x-app-layout>
    <div class="mx-auto max-w-2xl">
        <h1 class="text-xl font-semibold tracking-tight">
            Invitation ready for {{ $invitation->fullName() }}
        </h1>

        @if (session('status'))
            <div class="mt-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($link)
            <div class="mt-6 rounded-md border border-stone-300 bg-white p-5">
                <p class="text-sm font-medium">
                    @if ($invitation->delivery_channel === \App\Enums\InvitationChannel::CopyLink)
                        Copy this link and send it to them yourself
                    @else
                        The invitation is on its way. Here is the link as well, in case you need it.
                    @endif
                </p>

                <div class="mt-3 flex gap-2">
                    <input type="text" readonly value="{{ $link }}" id="invitation-link"
                           onclick="this.select()"
                           class="w-full rounded-md border-stone-300 bg-stone-50 px-3 py-2 font-mono text-xs">
                    <button type="button"
                            onclick="navigator.clipboard.writeText(document.getElementById('invitation-link').value); this.textContent='Copied';"
                            class="shrink-0 rounded-md bg-stone-800 px-3 py-2 text-sm font-medium text-white hover:bg-stone-700">
                        Copy
                    </button>
                </div>

                <p class="mt-3 text-sm text-stone-600">
                    It is personal to this one person, can be used once, and lapses on
                    {{ $invitation->expires_at->format('j F Y') }}.
                </p>

                <p class="mt-3 rounded-md bg-amber-50 px-3 py-2 text-sm text-amber-900">
                    This is the only time the link is shown. It is not stored anywhere in a
                    readable form, so it cannot be looked up again. If you lose it, issue a
                    fresh one and the old link stops working at that moment.
                </p>
            </div>
        @else
            <div class="mt-6 rounded-md border border-stone-200 bg-stone-50 p-5 text-sm text-stone-600">
                <p>The link has already been shown and cannot be displayed again.</p>
                <form method="POST" action="{{ route('admin.invitations.reissue', $invitation) }}" class="mt-4">
                    @csrf
                    <button type="submit" class="rounded-md border border-stone-300 bg-white px-3 py-1.5 text-sm font-medium hover:bg-stone-50">
                        Issue a fresh link
                    </button>
                </form>
            </div>
        @endif

        <div class="mt-8 rounded-md border border-stone-200 bg-white p-5 text-sm">
            <h2 class="font-medium">What happens next</h2>
            <ul class="mt-3 space-y-2 text-stone-600">
                <li>They open the link and confirm their own details. Opening it does not use it up.</li>
                <li>Nothing is final until they finish registering, and only then does the link stop working.</li>
                @if ($invitation->delivery_channel === \App\Enums\InvitationChannel::CopyLink)
                    <li>Because you are sending this yourself, the Portal will show it as issued, and later as accepted. It will not claim it was delivered, since it cannot see that.</li>
                @else
                    <li>Delivery is tracked, so you will see whether it reached them or bounced.</li>
                @endif
            </ul>
        </div>

        <p class="mt-6 text-sm">
            @if ($invitation->candidate_case_id)
                <a href="{{ route('admin.cases.show', $invitation->candidate_case_id) }}" class="underline underline-offset-2">Back to the case</a>
            @else
                <a href="{{ route('admin.queue.index') }}" class="underline underline-offset-2">Back to the queue</a>
            @endif
        </p>
    </div>
</x-app-layout>
