{{--
    A collaborator recommending someone.

    The page says plainly that the person will not be contacted. That is a
    promise the Institute made and the referrer should be able to see it, since
    they are often the only one who knows the conversation is happening.
--}}
<x-app-layout>
    <div class="mx-auto max-w-2xl">
        <h1 class="text-xl font-semibold tracking-tight">Recommend a professional</h1>

        <p class="mt-3 text-sm leading-relaxed text-stone-600">
            If you know someone whose work bears on the questions longer lives raise,
            the Institute would be glad to hear of them.
        </p>

        <p class="mt-3 rounded-md bg-stone-100 px-4 py-3 text-sm leading-relaxed text-stone-600">
            The Institute will not contact this person unless it decides to invite
            them. If anything needs clarifying beforehand, we will ask you rather
            than them.
        </p>

        <x-form-errors class="mt-6" />

        <form method="POST" action="{{ route('referrals.store') }}" class="mt-8 space-y-6">
            @csrf

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="candidate_first_name" class="block text-sm font-medium">Their first name</label>
                    <input id="candidate_first_name" name="candidate_first_name" type="text" required
                           maxlength="{{ \App\Support\FieldLimits::NAME }}"
                           value="{{ old('candidate_first_name') }}"
                           class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
                </div>
                <div>
                    <label for="candidate_last_name" class="block text-sm font-medium">Their family name</label>
                    <input id="candidate_last_name" name="candidate_last_name" type="text" required
                           maxlength="{{ \App\Support\FieldLimits::NAME }}"
                           value="{{ old('candidate_last_name') }}"
                           class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
                </div>
            </div>

            <div>
                <label for="candidate_email" class="block text-sm font-medium">Their email address</label>
                <input id="candidate_email" name="candidate_email" type="email" required
                       value="{{ old('candidate_email') }}"
                       class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
                <p class="mt-1 text-xs text-stone-500">Used only so the Institute can recognize them. Nothing is sent to it now.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="candidate_organisation" class="block text-sm font-medium">
                        Their organization <span class="font-normal text-stone-400">(optional)</span>
                    </label>
                    <input id="candidate_organisation" name="candidate_organisation" type="text"
                           maxlength="{{ \App\Support\FieldLimits::ORGANISATION }}"
                           value="{{ old('candidate_organisation') }}"
                           class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
                </div>
                <div>
                    <label for="candidate_country_code" class="block text-sm font-medium">
                        Country <span class="font-normal text-stone-400">(two-letter code, optional)</span>
                    </label>
                    <input id="candidate_country_code" name="candidate_country_code" type="text" maxlength="2"
                           value="{{ old('candidate_country_code') }}"
                           class="mt-1 w-full rounded-md border-stone-300 text-sm uppercase shadow-sm">
                </div>
            </div>

            <div>
                <label for="candidate_title" class="block text-sm font-medium">
                    Their professional title <span class="font-normal text-stone-400">(optional)</span>
                </label>
                <input id="candidate_title" name="candidate_title" type="text"
                       maxlength="{{ \App\Support\FieldLimits::TITLE }}"
                       value="{{ old('candidate_title') }}"
                       class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
            </div>

            {{-- The relationship question. Required, which is exactly why
                 "Prefer not to specify" is on the list. --}}
            <div class="rounded-md border border-stone-200 bg-stone-50 p-4">
                <label for="referral_relationship_id" class="block text-sm font-medium">
                    What is your relationship to this person?
                </label>
                <select id="referral_relationship_id" name="referral_relationship_id" required
                        class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
                    <option value="">Please choose</option>
                    @foreach ($relationships as $relationship)
                        <option value="{{ $relationship->id }}"
                                @selected(old('referral_relationship_id') == $relationship->id)>
                            {{ $relationship->label }}
                        </option>
                    @endforeach
                </select>

                <div class="mt-4">
                    <label for="relationship_detail" class="block text-sm font-medium">
                        If you chose "Other", how would you describe it?
                    </label>
                    <input id="relationship_detail" name="relationship_detail" type="text"
                           maxlength="{{ \App\Support\FieldLimits::ORGANISATION }}"
                           value="{{ old('relationship_detail') }}"
                           class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
                </div>

                <div class="mt-4">
                    <label for="acquaintance_note" class="block text-sm font-medium">
                        How do you know this person? <span class="font-normal text-stone-400">(optional)</span>
                    </label>
                    <textarea id="acquaintance_note" name="acquaintance_note" rows="3"
                              maxlength="{{ \App\Support\FieldLimits::NOTE }}"
                              class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">{{ old('acquaintance_note') }}</textarea>
                    <p class="mt-1 text-xs text-stone-500">
                        A sentence is plenty, for example "we worked together for six years".
                    </p>
                </div>

                <p class="mt-4 text-xs leading-relaxed text-stone-500">
                    Your answers in this section are seen by the Institute alone. They are
                    never shown to the person you are recommending, and never appear on any
                    profile.
                </p>
            </div>

            @if ($expertiseAreas->isNotEmpty())
                <fieldset>
                    <legend class="text-sm font-medium">Their areas of expertise <span class="font-normal text-stone-400">(optional)</span></legend>
                    <div class="mt-2 grid gap-2 sm:grid-cols-2">
                        @foreach ($expertiseAreas as $area)
                            <label class="flex items-start gap-2 text-sm text-stone-700">
                                <input type="checkbox" name="candidate_expertise[]" value="{{ $area->id }}"
                                       @checked(in_array($area->id, old('candidate_expertise', [])))
                                       class="mt-0.5 rounded border-stone-300">
                                <span>{{ $area->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endif

            <div>
                <label for="reason" class="block text-sm font-medium">Why are you recommending them?</label>
                <textarea id="reason" name="reason" rows="5" required
                          class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">{{ old('reason') }}</textarea>
            </div>

            <div class="border-t border-stone-200 pt-6">
                <button type="submit"
                        class="rounded-md bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
                    Send recommendation
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
