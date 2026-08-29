{{--
    Registration. The invited person confirms their own details rather than
    being stuck with what the Institute typed: the Founder is the authority on
    admission, they are the authority on the facts of their own life.

    Reaching this page does not consume the invitation. Only submitting it does.
--}}
<x-public-layout title="Complete your registration — Active Longevity Institute">
    <h1 class="text-xl font-semibold tracking-tight">
        {{ \App\Support\Salutation::greeting($invitation->salutationName()) }}
    </h1>

    {{-- Written as one expression rather than an inline directive: Blade does
         not compile a directive that follows a word character. --}}
    <p class="mt-4 text-sm leading-relaxed text-stone-600">
        You have been invited to join the collaborator community of the Active
        Longevity Institute{{ $referrerName ? ', on the recommendation of '.$referrerName : '' }}.
    </p>

    @if (filled($invitation->personal_message))
        <blockquote class="mt-5 border-l-2 border-stone-300 pl-4 text-sm leading-relaxed text-stone-700">
            {{ $invitation->personal_message }}
        </blockquote>
    @endif

    <p class="mt-5 text-sm leading-relaxed text-stone-600">
        Please confirm your details below. We have filled in what we were told, and
        anything that is not quite right is yours to correct.
    </p>

    <x-form-errors class="mt-6" />

    <form method="POST" action="{{ route('invitations.accept', ['token' => $token]) }}" class="mt-8 space-y-6">
        @csrf

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label for="first_name" class="block text-sm font-medium">First name</label>
                <input id="first_name" name="first_name" type="text" required
                       maxlength="{{ \App\Support\FieldLimits::NAME }}"
                       value="{{ old('first_name', $invitation->first_name) }}"
                       class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
            </div>
            <div>
                <label for="middle_name" class="block text-sm font-medium">
                    Middle name <span class="font-normal text-stone-400">(optional)</span>
                </label>
                <input id="middle_name" name="middle_name" type="text"
                       maxlength="{{ \App\Support\FieldLimits::NAME }}"
                       value="{{ old('middle_name', $invitation->middle_name) }}"
                       class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
            </div>
            <div>
                <label for="last_name" class="block text-sm font-medium">Family name</label>
                <input id="last_name" name="last_name" type="text" required
                       maxlength="{{ \App\Support\FieldLimits::NAME }}"
                       value="{{ old('last_name', $invitation->last_name) }}"
                       class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
            </div>
        </div>

        {{-- Never phrased as "if different from your first name". --}}
        <div>
            <label for="preferred_name" class="block text-sm font-medium">
                How would you like the Portal to address you?
                <span class="font-normal text-stone-400">(optional)</span>
            </label>
            <input id="preferred_name" name="preferred_name" type="text"
                   maxlength="{{ \App\Support\FieldLimits::NAME }}"
                   value="{{ old('preferred_name', $invitation->preferred_name) }}"
                   class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
            <p class="mt-1 text-xs text-stone-500">
                We will use this when we write to you. Your full name remains your name of record.
            </p>
        </div>

        @if ($invitation->email === null)
            {{-- The copy-link route. The Institute never held an address for
                 this person, so they supply their own. --}}
            <div>
                <label for="email" class="block text-sm font-medium">Email address</label>
                <input id="email" name="email" type="email" required value="{{ old('email') }}"
                       class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
                <p class="mt-1 text-xs text-stone-500">This is the address you will sign in with.</p>
            </div>
        @else
            <div>
                <p class="block text-sm font-medium">Email address</p>
                <p class="mt-1 rounded-md bg-stone-50 px-3 py-2 text-sm text-stone-600">{{ $invitation->email }}</p>
                <p class="mt-1 text-xs text-stone-500">
                    This is the address your invitation was sent to, and the one you will sign in with.
                </p>
            </div>
        @endif

        <div>
            <label for="password" class="block text-sm font-medium">Choose a password</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                   class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium">Confirm your password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required
                   autocomplete="new-password"
                   class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
        </div>

        <div class="border-t border-stone-200 pt-6">
            <label class="flex items-start gap-3 text-sm text-stone-700">
                <input type="checkbox" name="privacy_consent" value="1" required
                       class="mt-0.5 rounded border-stone-300">
                <span>I have read the Institute's privacy notice and agree to it.</span>
            </label>

            <button type="submit"
                    class="mt-6 rounded-md bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
                Complete registration
            </button>
        </div>
    </form>
</x-public-layout>
