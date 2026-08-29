{{--
    Careful wording. The referrer is told only that the Institute has the
    recommendation. They are told nothing about what is decided, and will hear
    again only if an invitation is sent or a question needs asking.
--}}
<x-app-layout>
    <div class="mx-auto max-w-2xl">
        <h1 class="text-xl font-semibold tracking-tight">Thank you</h1>

        <p class="mt-4 text-sm leading-relaxed text-stone-600">
            Your recommendation{{ $candidateName ? ' of '.$candidateName : '' }} has reached the Institute.
        </p>

        <p class="mt-3 text-sm leading-relaxed text-stone-600">
            Nothing has been sent to them. If the Institute needs to understand
            anything further, it will write to you rather than to them, and you will
            hear from us if an invitation is issued.
        </p>

        <p class="mt-6 text-sm">
            <a href="{{ route('referrals.create') }}" class="underline underline-offset-2">Recommend someone else</a>
            <span class="mx-2 text-stone-300">|</span>
            <a href="{{ route('home') }}" class="underline underline-offset-2">Back to the Portal</a>
        </p>
    </div>
</x-app-layout>
