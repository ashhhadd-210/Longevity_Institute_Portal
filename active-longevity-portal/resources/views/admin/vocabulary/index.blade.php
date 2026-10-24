{{--
    The Institute deciding what joins its vocabulary.

    One queue for Fields, Specialties and credentials. Each proposal arrives with
    the near matches the vocabulary already holds, so mapping is one click and
    approving a duplicate is hard to do by accident. The wording can be
    corrected before approving, and because collaborators select entries rather
    than typing text, the corrected wording is what every profile shows.
--}}
<x-app-layout>
    <div class="mx-auto max-w-4xl">
        <h1 class="text-2xl font-semibold text-stone-800">Proposed vocabulary</h1>
        <p class="mt-1 text-sm text-stone-600">
            Terms collaborators entered because nothing in the Registers fit. Their profiles
            are already saved; nothing here is holding anybody up.
        </p>

        @if (session('status'))
            <div class="mt-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('status') }}</div>
        @endif
        @error('proposal')
            <div class="mt-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">{{ $message }}</div>
        @enderror

        @if ($pending->isEmpty())
            <div class="mt-8 rounded-lg border border-stone-200 bg-white p-6 text-sm text-stone-600">
                Nothing is waiting. Every term a collaborator has proposed has been decided.
            </div>
        @endif

        @foreach ([['field', 'Fields'], ['specialty', 'Specialties'], ['credential', 'Credentials']] as [$kind, $heading])
            @php $group = $pending->get($kind, collect()); @endphp
            @if ($group->isNotEmpty())
                <h2 class="mt-10 text-xs font-medium uppercase tracking-wide text-stone-500">{{ $heading }} · {{ $group->count() }}</h2>

                <div class="mt-3 space-y-4">
                    @foreach ($group as $proposal)
                        @php $near = $suggestions[$proposal->id] ?? []; @endphp
                        <div class="rounded-lg border border-stone-200 bg-white p-5">
                            <div class="flex flex-wrap items-baseline justify-between gap-2">
                                <div>
                                    <p class="text-lg font-medium text-stone-800">
                                        {{ $proposal->term }}
                                        @if (filled($proposal->meaning))
                                            <span class="text-base font-normal text-stone-600">— {{ $proposal->meaning }}</span>
                                        @endif
                                        @if (filled($proposal->jurisdiction))
                                            <span class="text-sm font-normal text-stone-500">({{ $proposal->jurisdiction }})</span>
                                        @endif
                                    </p>
                                    <p class="mt-0.5 text-xs text-stone-500">
                                        Proposed by {{ $proposal->proposedBy?->fullName() }}
                                        on {{ $proposal->created_at?->format('j F Y') }}
                                    </p>
                                </div>
                            </div>

                            @if ($near !== [])
                                <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 p-3">
                                    <p class="text-xs font-medium text-amber-900">The vocabulary already holds something close</p>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach ($near as $candidate)
                                            <form method="POST" action="{{ route('admin.vocabulary.map', $proposal) }}">
                                                @csrf
                                                <input type="hidden" name="target_id" value="{{ $candidate['id'] }}">
                                                <button type="submit"
                                                        class="rounded-md border border-amber-300 bg-white px-3 py-1 text-xs hover:bg-amber-100"
                                                        title="Match this proposal to the existing term">
                                                    Same as: {{ $candidate['label'] }}
                                                </button>
                                            </form>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="mt-4 grid gap-4 md:grid-cols-2">
                                <form method="POST" action="{{ route('admin.vocabulary.approve', $proposal) }}" class="space-y-2">
                                    @csrf
                                    <p class="text-sm font-medium text-stone-800">Approve</p>
                                    <input type="text" name="term" value="{{ $proposal->term }}" maxlength="300"
                                           class="w-full rounded-md border-stone-300 text-sm shadow-sm"
                                           aria-label="Wording, corrected if needed">
                                    @if ($kind === 'credential')
                                        <input type="text" name="meaning" value="{{ $proposal->meaning }}" maxlength="300"
                                               class="w-full rounded-md border-stone-300 text-sm shadow-sm"
                                               aria-label="Full meaning, corrected if needed">
                                        <input type="text" name="issuing_body" maxlength="200" placeholder="Issuing body or professional organization (optional)"
                                               class="w-full rounded-md border-stone-300 text-sm shadow-sm"
                                               aria-label="Issuing body">
                                        <input type="text" name="description" maxlength="600" placeholder="One plain line on what it represents (optional)"
                                               class="w-full rounded-md border-stone-300 text-sm shadow-sm"
                                               aria-label="Plain-language description">
                                    @endif
                                    @if ($kind === 'specialty')
                                        <select name="field_ids[]" multiple size="4"
                                                class="w-full rounded-md border-stone-300 text-sm shadow-sm"
                                                aria-label="Fields this Specialty is recognizable within, for search">
                                            @foreach ($fields as $field)
                                                <option value="{{ $field->id }}">{{ $field->name }}</option>
                                            @endforeach
                                        </select>
                                        <p class="text-xs text-stone-500">Fields it should be found under. For search only; it assigns nobody a Field.</p>
                                    @endif
                                    <p class="text-xs text-stone-500">Correct the wording here if you wish. Your wording is what every profile will show.</p>
                                    <button type="submit"
                                            class="rounded-md bg-stone-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-stone-700">
                                        Approve
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.vocabulary.decline', $proposal) }}" class="space-y-2"
                                      onsubmit="return confirm('Decline this term? The collaborator will be emailed that it was not added, with your note if you wrote one.');">
                                    @csrf
                                    <p class="text-sm font-medium text-stone-800">Decline</p>
                                    <textarea name="note" rows="2" maxlength="2000"
                                              placeholder="A short note to the collaborator (optional)"
                                              class="w-full rounded-md border-stone-300 text-sm shadow-sm"></textarea>
                                    <p class="text-xs text-stone-500">The collaborator is emailed that the term was not added, with your note if you write one. It does not appear on their profile.</p>
                                    <button type="submit"
                                            class="rounded-md border border-stone-300 px-3 py-1.5 text-sm font-medium hover:bg-stone-50">
                                        Decline
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endforeach

        @if ($recentlySettled->isNotEmpty())
            <h2 class="mt-12 text-xs font-medium uppercase tracking-wide text-stone-500">Recently decided</h2>
            <ul class="mt-3 divide-y divide-stone-100 rounded-lg border border-stone-200 bg-white text-sm">
                @foreach ($recentlySettled as $settled)
                    <li class="flex flex-wrap justify-between gap-2 px-4 py-2">
                        <span class="text-stone-800">{{ $settled->term }} <span class="text-xs text-stone-500">({{ $settled->kind }})</span></span>
                        <span class="text-xs text-stone-500">{{ ucfirst($settled->status) }} · {{ $settled->reviewed_at?->format('j M') }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-app-layout>
