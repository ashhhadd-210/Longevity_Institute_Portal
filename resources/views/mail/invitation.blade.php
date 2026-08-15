<x-mail::message>
Dear {{ $firstName }},

You have been invited to join the Active Longevity Institute's Collaborator
Portal — a private professional community of researchers, practitioners and
experts working across the disciplines of healthy aging and longevity.

This invitation is personal to you and can be used once.

<x-mail::button :url="$acceptanceUrl">
Accept your invitation
</x-mail::button>

The invitation remains valid until {{ $expiresAt->toFormattedDateString() }}.
If it expires, the Institute can issue a new one.

If you were not expecting this invitation, you can safely disregard this
message.

With best regards,<br>
Active Longevity Institute
</x-mail::message>
