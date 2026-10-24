{{--
    The community directory.

    A list rather than a wide table, because tables do not survive a narrow
    screen intact and a good number of these people will be on a phone.

    Only Fields containing somebody are offered. Counts are off while the
    community is small: "Medicine 1" reads as a disappointment rather than as
    information.
--}}
<x-app-layout>
    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6">

        <h1 class="text-2xl font-semibold text-stone-800">The collaborator community</h1>
        <p class="mt-1 text-sm text-stone-600">
            Search by name, by Professional Identity, or by Field and Specialty.
        </p>

        <form method="GET" action="{{ route('directory.index') }}" class="mt-6 space-y-3">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}"
                   placeholder="A name, a title, or an area of work"
                   class="w-full rounded-md border-stone-300 text-sm shadow-sm focus:border-stone-500 focus:ring-stone-500">

            <div class="grid gap-3 sm:grid-cols-3">
                <select name="field" class="rounded-md border-stone-300 text-sm shadow-sm">
                    <option value="">Any Field</option>
                    @foreach ($fields as $field)
                        <option value="{{ $field->id }}" @selected(($filters['field'] ?? null) == $field->id)>
                            {{ $field->name }}
                        </option>
                    @endforeach
                </select>

                <select name="country" class="rounded-md border-stone-300 text-sm shadow-sm">
                    <option value="">Any country</option>
                    @foreach ($countries as $country)
                        <option value="{{ $country }}" @selected(($filters['country'] ?? null) === $country)>
                            {{ \App\Support\Countries::name($country) }}
                        </option>
                    @endforeach
                </select>

                <div class="flex gap-2">
                    <button type="submit"
                            class="rounded-md bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
                        Search
                    </button>
                    @if (array_filter($filters))
                        <a href="{{ route('directory.index') }}"
                           class="rounded-md border border-stone-300 px-4 py-2 text-sm hover:bg-white">Clear</a>
                    @endif
                </div>
            </div>
        </form>

        <p class="mt-6 text-sm text-stone-500">
            {{ $profiles->total() }} {{ \Illuminate\Support\Str::plural('collaborator', $profiles->total()) }}
        </p>

        @if ($profiles->isEmpty())
            <div class="mt-4 rounded-lg border border-stone-200 bg-stone-50 p-6 text-sm text-stone-600">
                @if (array_filter($filters))
                    <p>Nothing matched that search.</p>
                    <p class="mt-1">
                        The community is still small, so a term that finds nobody today may well
                        find somebody next month.
                    </p>
                @else
                    <p>The directory is not yet populated.</p>
                    <p class="mt-1">
                        Collaborators appear here once their profile carries enough for somebody
                        else to recognize them.
                    </p>
                @endif
            </div>
        @else
            <ul class="mt-4 divide-y divide-stone-100 rounded-lg border border-stone-200 bg-white">
                @foreach ($profiles as $entry)
                    @php $person = $entry->collaborator?->user; @endphp
                    @php $credentials = $entry->approvedCredentials; @endphp
                    <li class="p-4 sm:p-5">
                        <a href="{{ route('profile.show', $entry) }}" class="flex items-start gap-4">
                            @if (filled($entry->photo_path))
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($entry->photo_path) }}"
                                     alt="" class="h-20 w-15 flex-none rounded border border-stone-200 object-cover" style="width:3.75rem">
                            @endif
                            <div class="min-w-0">
                                <p class="font-medium text-stone-800">
                                    {{ $person?->fullName() }}@if ($credentials->isNotEmpty())<span class="font-normal text-stone-500">, {{ $credentials->pluck('acronym')->join(', ') }}</span>@endif
                                </p>

                                @if (filled($entry->professional_title))
                                    <p class="mt-0.5 text-sm text-stone-600">{{ $entry->professional_title }}</p>
                                @endif

                                @if (filled($entry->country_code))
                                    <p class="mt-0.5 text-xs text-stone-500">{{ \App\Support\Countries::name($entry->country_code) }}</p>
                                @endif
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="mt-6">{{ $profiles->links() }}</div>
        @endif
    </div>
</x-app-layout>
