<x-mail::message>
{{ $greeting }},

@if ($asksAboutSomeoneElse)
Thank you for recommending a colleague to the Active Longevity Institute. Before
the Institute considers the recommendation further, there is something we would
like to understand better.
@else
Thank you for your interest in the Active Longevity Institute. Before your
application is considered further, there is something we would like to
understand better.
@endif

{{ $question }}

A reply to this message is all that is needed. There is no form to complete.

@if ($asksAboutSomeoneElse)
The person you recommended has not been contacted, and will not be unless the
Institute decides to invite them.
@endif

With best regards,<br>
Active Longevity Institute
</x-mail::message>
