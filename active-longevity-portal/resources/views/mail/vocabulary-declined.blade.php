<x-mail::message>
{{ $greeting }},

Thank you for proposing "{{ $proposed }}" for the Institute's vocabulary. After
review, it has not been added, so it will not appear on your profile.

@if (filled($note))
{{ $note }}

@endif
Your profile is otherwise unaffected. You are welcome to choose an existing
@if ($kind === 'credential')
credential from the library, or to propose a different one.
@else
term, or to propose a different one.
@endif

<x-mail::button :url="$url">
Open your profile
</x-mail::button>

With best regards,<br>
Active Longevity Institute
</x-mail::message>
