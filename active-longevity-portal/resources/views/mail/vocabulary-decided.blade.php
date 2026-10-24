{{--
    A term the collaborator proposed has been settled in their favor. Whether
    it was approved under their wording, approved under corrected wording, or
    matched to something the Institute already held, the result for them is
    the same: it is on their profile now.
--}}
<x-mail::message>
{{ $greeting }},

@if ($wasMapped)
You entered "{{ $proposed }}" on your profile because nothing in the Institute's
vocabulary quite fit. It turned out the Institute already holds the same
thing under different wording, and it now appears on your profile as
**{{ $shownAs }}**.
@elseif ($shownAs !== $proposed)
You proposed "{{ $proposed }}" for the Institute's vocabulary. It has been
approved, with the wording standardized to **{{ $shownAs }}**, and it now
appears on your profile.
@else
You proposed "{{ $proposed }}" for the Institute's vocabulary. It has been
approved and now appears on your profile.
@endif

Nothing is needed from you. Thank you for helping the vocabulary grow from
what collaborators actually do.

With best regards,<br>
Active Longevity Institute
</x-mail::message>
