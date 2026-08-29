{{--
    Honest about what happens next. The Institute reads applications itself and
    does not promise a date, so this page does not invent one.
--}}
<x-public-layout title="Application received — Active Longevity Institute">
    <h1 class="text-xl font-semibold tracking-tight">
        {{ \App\Support\Salutation::greeting($applicantName) }}, and thank you
    </h1>

    <p class="mt-4 text-sm leading-relaxed text-stone-600">
        Your application has reached the Institute and will be read by a person, not
        sorted by a machine.
    </p>

    <p class="mt-3 text-sm leading-relaxed text-stone-600">
        If anything needs clarifying, we will write to you at the address you gave.
        If the Institute decides to invite you, you will receive an invitation with
        your own link to complete your profile.
    </p>

    <p class="mt-6 text-sm text-stone-500">
        There is nothing further you need to do for now.
    </p>
</x-public-layout>
