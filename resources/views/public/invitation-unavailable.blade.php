{{--
    A link that no longer works is met with courtesy, never an error page.
    Arriving here is not a mistake the visitor made (agreed 25 August 2026).

    An unknown token and a lapsed one are shown the same words. There is no
    reason to help anybody tell a wrong guess from a real invitation.
--}}
<x-public-layout title="This invitation link — Active Longevity Institute">
    @if ($reason === 'already_registered')
        <h1 class="text-xl font-semibold tracking-tight">This invitation has already been used</h1>

        <p class="mt-4 text-sm leading-relaxed text-stone-600">
            An account has already been created with this invitation, which usually
            means you have registered before.
        </p>

        <p class="mt-3 text-sm leading-relaxed text-stone-600">
            You can <a href="{{ route('login') }}" class="underline underline-offset-2">sign in here</a>.
            If you did not expect this, please reply to the message that brought you
            here and the Institute will look into it.
        </p>
    @elseif ($reason === 'withdrawn')
        <h1 class="text-xl font-semibold tracking-tight">This invitation is no longer open</h1>

        <p class="mt-4 text-sm leading-relaxed text-stone-600">
            The Institute has withdrawn this invitation. That is sometimes simply
            because a newer one was issued in its place.
        </p>

        <p class="mt-3 text-sm leading-relaxed text-stone-600">
            If you were expecting to join, please reply to the message that brought
            you here and someone will help.
        </p>
    @else
        <h1 class="text-xl font-semibold tracking-tight">This invitation link has lapsed</h1>

        <p class="mt-4 text-sm leading-relaxed text-stone-600">
            Invitation links are personal to one person and last for a limited time,
            so this one can no longer be used.
        </p>

        <p class="mt-3 text-sm leading-relaxed text-stone-600">
            Nothing has gone wrong and nothing is lost. Replying to the message that
            brought you here is enough, and a fresh link can be issued in a moment.
        </p>
    @endif
</x-public-layout>
