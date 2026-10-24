{{--
    The public application. Deliberately short: everything the Institute needs
    in order to decide whether to open a conversation, and nothing more. The
    rest a collaborator supplies later, about their own life, in their own words.
--}}
<x-public-layout title="Apply to join — Active Longevity Institute">
    <h1 class="text-xl font-semibold tracking-tight">Apply to join the collaborator community</h1>

    <p class="mt-3 text-sm leading-relaxed text-stone-600">
        The Institute brings together professionals whose work touches the questions
        that longer lives raise. Tell us a little about yourself and why you would
        like to take part, and someone will read it properly.
    </p>

    <x-form-errors class="mt-6" />

    <form method="POST" action="{{ route('applications.store') }}" class="mt-8 space-y-6">
        @csrf

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label for="first_name" class="block text-sm font-medium">First name</label>
                <input id="first_name" name="first_name" type="text" required
                       maxlength="{{ \App\Support\FieldLimits::NAME }}"
                       value="{{ old('first_name') }}"
                       class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
            </div>
            <div>
                <label for="middle_name" class="block text-sm font-medium">
                    Middle name <span class="font-normal text-stone-400">(optional)</span>
                </label>
                <input id="middle_name" name="middle_name" type="text"
                       maxlength="{{ \App\Support\FieldLimits::NAME }}"
                       value="{{ old('middle_name') }}"
                       class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
            </div>
            <div>
                <label for="last_name" class="block text-sm font-medium">Family name</label>
                <input id="last_name" name="last_name" type="text" required
                       maxlength="{{ \App\Support\FieldLimits::NAME }}"
                       value="{{ old('last_name') }}"
                       class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
            </div>
        </div>

        <div>
            <label for="email" class="block text-sm font-medium">Email address</label>
            <input id="email" name="email" type="email" required value="{{ old('email') }}"
                   class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="organisation_text" class="block text-sm font-medium">
                    Organization <span class="font-normal text-stone-400">(optional)</span>
                </label>
                <input id="organisation_text" name="organisation_text" type="text"
                       maxlength="{{ \App\Support\FieldLimits::ORGANISATION }}"
                       value="{{ old('organisation_text') }}"
                       class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
            </div>
            <div>
                <label for="country_code" class="block text-sm font-medium">
                    Country <span class="font-normal text-stone-400">(two-letter code, optional)</span>
                </label>
                <input id="country_code" name="country_code" type="text" maxlength="2"
                       value="{{ old('country_code') }}"
                       class="mt-1 w-full rounded-md border-stone-300 text-sm uppercase shadow-sm">
            </div>
        </div>

        <div>
            <label for="professional_title" class="block text-sm font-medium">
                Professional title <span class="font-normal text-stone-400">(optional)</span>
            </label>
            <input id="professional_title" name="professional_title" type="text"
                   maxlength="{{ \App\Support\FieldLimits::TITLE }}"
                   value="{{ old('professional_title') }}"
                   class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
            {{-- Stated plainly, because people abbreviate their own titles when
                 they suspect a form will not accept the whole thing. --}}
            <p class="mt-1 text-xs text-stone-500">
                Please write your title in full. There is room for {{ \App\Support\FieldLimits::TITLE }} characters,
                so named chairs and joint appointments do not need to be shortened.
            </p>
        </div>

        @if ($expertiseAreas->isNotEmpty())
            <fieldset>
                <legend class="text-sm font-medium">Areas of expertise <span class="font-normal text-stone-400">(optional)</span></legend>
                <div class="mt-2 grid gap-2 sm:grid-cols-2">
                    @foreach ($expertiseAreas as $area)
                        <label class="flex items-start gap-2 text-sm text-stone-700">
                            <input type="checkbox" name="expertise_claimed[]" value="{{ $area->id }}"
                                   @checked(in_array($area->id, old('expertise_claimed', [])))
                                   class="mt-0.5 rounded border-stone-300">
                            <span>{{ $area->name }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
        @endif

        <div>
            <label for="motivation" class="block text-sm font-medium">Why would you like to take part?</label>
            <textarea id="motivation" name="motivation" rows="6" required
                      class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">{{ old('motivation') }}</textarea>
        </div>

        <div class="border-t border-stone-200 pt-6">
            <button type="submit"
                    class="rounded-md bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
                Send application
            </button>
        </div>
    </form>
</x-public-layout>
