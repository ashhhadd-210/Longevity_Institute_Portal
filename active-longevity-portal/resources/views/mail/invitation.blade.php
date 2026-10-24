{{--
    The greeting comes from the shared salutation rule, so this message and
    every other one address the same person identically. When the Portal does
    not confidently know a name it greets without one rather than awkwardly.
--}}
<x-mail::message>
{{ $greeting }},

You have been invited to join the collaborator community of the Active Longevity
Institute, a curated group of researchers, practitioners and professionals whose
work bears on the questions that longer lives raise.

@if ($referrerName)
Your name reached us through {{ $referrerName }}.

@endif
@if ($personalMessage)
{{ $personalMessage }}

@endif
This invitation is personal to you and can be used once.

<x-mail::button :url="$acceptanceUrl">
Accept your invitation
</x-mail::button>

The link remains open until {{ $expiresAt->toFormattedDateString() }}. If it
lapses before you use it, the Institute can issue a new one, so nothing is lost.

If you were not expecting this, you can safely disregard this message and
nothing further will happen.

With best regards,<br>
Active Longevity Institute
</x-mail::message>
