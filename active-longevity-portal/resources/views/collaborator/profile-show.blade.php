{{--
    A collaborator's profile, as another collaborator sees it.

    Whatever the Portal accepted, the Portal shows: long titles wrap onto
    further lines rather than being cut off. Unused fields disappear entirely
    rather than appearing as empty labels, so a profile with three things filled
    in reads as deliberate rather than unfinished.

    Links show their label only. The address is never printed, though the
    browser reveals it on hover, which is what makes that safe.

    City and region are collected but never appear here. That was the promise
    made when they were asked for.
--}}
<x-app-layout>
    @php
        $person = $profile->collaborator?->user;
        $credentials = $profile->approvedCredentials;
    @endphp

    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6">

        <a href="{{ route('directory.index') }}" class="text-sm underline underline-offset-2">Back to the directory</a>

        <header class="mt-6 flex flex-col gap-5 sm:flex-row sm:items-start">
            @if (filled($profile->photo_path))
                <img src="{{ \Illuminate\Support\Facades\Storage::url($profile->photo_path) }}"
                     alt="{{ $person?->fullName() }}"
                     class="h-32 w-24 flex-none rounded-md border border-stone-200 object-cover">
            @endif

            <div class="min-w-0">
                <h1 class="text-2xl font-semibold text-stone-800">
                    {{ $person?->fullName() }}@if ($credentials->isNotEmpty())<span class="text-stone-500">, {{ $credentials->pluck('acronym')->join(', ') }}</span>@endif
                </h1>

                @if (filled($person?->preferred_name) && $person->preferred_name !== $person->first_name)
                    <p class="mt-0.5 text-sm text-stone-500">Goes by {{ $person->preferred_name }}</p>
                @endif

                @if (filled($profile->professional_title))
                    <p class="mt-2 text-stone-700">{{ $profile->professional_title }}</p>
                @endif

                @if ($profile->organisation)
                    <p class="mt-1 text-sm text-stone-600">{{ $profile->organisation->name }}</p>
                @endif

                @if (filled($profile->country_code))
                    <p class="mt-1 text-sm text-stone-500">{{ \App\Support\Countries::name($profile->country_code) }}</p>
                @endif
            </div>
        </header>

        @if ($credentials->isNotEmpty())
            <p class="mt-3 text-xs text-stone-500">
                @foreach ($credentials as $credential)
                    <span class="mr-3">{{ $credential->acronym }} — {{ $credential->describe() }}</span>
                @endforeach
            </p>
        @endif

        @if (filled($profile->one_line_summary))
            <p class="mt-6 text-lg leading-relaxed text-stone-700">{{ $profile->one_line_summary }}</p>
        @endif

        @if ($profile->currentPositions->isNotEmpty())
            <section class="mt-8">
                <h2 class="text-xs font-medium uppercase tracking-wide text-stone-500">Current positions</h2>
                <ul class="mt-2 space-y-2">
                    @foreach ($profile->currentPositions as $entry)
                        @php $pos = $entry->payload; @endphp
                        <li>
                            @if (filled($pos['title'] ?? null))
                                <p class="text-stone-800">{{ $pos['title'] }}</p>
                            @endif
                            @php
                                $where = array_filter([
                                    $pos['organisation'] ?? null,
                                    $pos['department'] ?? null,
                                    \App\Support\Countries::name($pos['country'] ?? null),
                                ]);
                            @endphp
                            @if ($where !== [] || filled($pos['start_year'] ?? null))
                                <p class="text-sm text-stone-600">
                                    {{ implode(', ', $where) }}@if (filled($pos['start_year'] ?? null))@if ($where !== []) · @endif since {{ $pos['start_year'] }}@endif
                                </p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if ($profile->fields->isNotEmpty() || $profile->specialties->isNotEmpty())
            <section class="mt-8">
                <h2 class="text-xs font-medium uppercase tracking-wide text-stone-500">Fields and Specialties</h2>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach ($profile->fields as $field)
                        <a href="{{ route('directory.index', ['field' => $field->id]) }}"
                           class="rounded-full bg-stone-100 px-3 py-1 text-sm text-stone-800 hover:bg-stone-200">
                            {{ $field->name }}
                        </a>
                    @endforeach
                    @foreach ($profile->specialties as $specialty)
                        <span class="rounded-full border border-stone-200 px-3 py-1 text-sm text-stone-600"
                              title="{{ $specialty->disciplines->pluck('name')->join(', ') }}">
                            {{ $specialty->name }}
                        </span>
                    @endforeach
                </div>
            </section>
        @endif

        @if (filled($profile->about))
            <section class="mt-8">
                <h2 class="text-xs font-medium uppercase tracking-wide text-stone-500">Additional Information</h2>
                <div class="mt-2 whitespace-pre-line leading-relaxed text-stone-700">{{ $profile->about }}</div>
            </section>
        @endif

        @if ($profile->links->isNotEmpty())
            <section class="mt-8">
                <h2 class="text-xs font-medium uppercase tracking-wide text-stone-500">Links</h2>
                <ul class="mt-2 space-y-1">
                    @foreach ($profile->links as $link)
                        <li>
                            <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer"
                               class="text-sm underline underline-offset-2">{{ $link->label }}</a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if (filled($profile->collaboration_availability))
            <section class="mt-8 rounded-lg border border-stone-200 bg-stone-50 p-4">
                <h2 class="text-xs font-medium uppercase tracking-wide text-stone-500">Open to</h2>
                <p class="mt-1 text-sm text-stone-700">{{ $profile->collaboration_availability }}</p>
            </section>
        @endif

        {{--
            Registering an interest in working together. What the first Beta uses
            instead of messaging: a record and a note, seen by the Institute, which
            passes it on. Offered only where the person has said they are open to
            approaches, never to oneself, and once per pair.
        --}}
        @php
            $viewerProfile = auth()->user()?->collaborator?->profile;
            $isSelf = $viewerProfile !== null && $viewerProfile->id === $profile->id;
            $alreadySent = $viewerProfile !== null && \App\Models\CollaborationInterest::query()
                ->where('from_profile_id', $viewerProfile->id)
                ->where('to_profile_id', $profile->id)
                ->exists();
        @endphp

        @if (! $isSelf && $viewerProfile !== null)
            <section class="mt-8 rounded-lg border border-stone-200 bg-white p-5">
                @if (session('status'))
                    <p class="mb-3 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-900">{{ session('status') }}</p>
                @endif

                @if ($alreadySent)
                    <p class="text-sm text-stone-600">
                        You have registered an interest in working with {{ $person?->preferred_name ?: $person?->first_name }}.
                        The Institute has it and will pass it on.
                    </p>
                @elseif ($profile->accepts_collaboration_interest)
                    <h2 class="text-sm font-medium text-stone-800">Interested in working together?</h2>
                    <p class="mt-1 text-sm text-stone-600">
                        Let the Institute know, and it will pass your interest on.
                    </p>
                    <form method="POST" action="{{ route('profile.interest', $profile) }}" class="mt-3 space-y-2">
                        @csrf
                        <textarea name="note" rows="2" maxlength="1000"
                                  placeholder="Optional: what you would like to explore together"
                                  class="w-full rounded-md border-stone-300 text-sm shadow-sm">{{ old('note') }}</textarea>
                        <button type="submit"
                                class="rounded-md bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
                            Register an interest
                        </button>
                    </form>
                @else
                    <p class="text-sm text-stone-500">
                        {{ $person?->preferred_name ?: $person?->first_name }} is not currently taking approaches through the Portal.
                    </p>
                @endif
            </section>
        @endif
    </div>
</x-app-layout>
