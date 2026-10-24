{{--
    The Institute maintaining its own vocabulary: Fields, Specialties and
    aliases, without a developer. Rename changes every profile at once. Retire
    stops offering a term and leaves it where it already is.

    Built to stay usable at several hundred entries: one search box narrows all
    three lists, and each row is its own small form.
--}}
<x-app-layout>
    <div class="mx-auto max-w-5xl">
        <h1 class="text-2xl font-semibold text-stone-800">Vocabulary Registers</h1>
        <p class="mt-1 text-sm text-stone-600">
            Fields, Specialties and aliases. Renaming a term changes every profile that uses it.
            Retiring a term stops offering it to anybody new and leaves it on the profiles that
            already hold it.
        </p>

        @if (session('status'))
            <div class="mt-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('status') }}</div>
        @endif
        @foreach ($errors->all() as $error)
            <div class="mt-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">{{ $error }}</div>
        @endforeach

        <form method="GET" action="{{ route('admin.taxonomy.index') }}" class="mt-6 flex gap-2">
            <input type="search" name="q" value="{{ $q }}" placeholder="Narrow all three lists"
                   class="w-full rounded-md border-stone-300 text-sm shadow-sm sm:max-w-sm">
            <button type="submit" class="rounded-md border border-stone-300 px-3 py-2 text-sm hover:bg-white">Search</button>
            @if ($q !== '')
                <a href="{{ route('admin.taxonomy.index') }}" class="rounded-md px-3 py-2 text-sm text-stone-500 underline">Clear</a>
            @endif
        </form>

        {{-- ================================================================ --}}
        <section class="mt-10">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <h2 class="text-lg font-medium text-stone-800">Fields <span class="text-sm font-normal text-stone-500">· {{ $fields->count() }}</span></h2>
            </div>

            <form method="POST" action="{{ route('admin.taxonomy.fields.store') }}" class="mt-3 grid gap-2 rounded-md border border-dashed border-stone-300 bg-white p-3 sm:grid-cols-6">
                @csrf
                <input type="text" name="name" required maxlength="100" placeholder="New Field"
                       class="rounded-md border-stone-300 text-sm shadow-sm sm:col-span-2">
                <input type="text" name="descriptor" maxlength="300" placeholder="One line that helps somebody choose it over its neighbors (optional)"
                       class="rounded-md border-stone-300 text-sm shadow-sm sm:col-span-3">
                <button type="submit" class="rounded-md bg-stone-800 px-3 py-2 text-sm font-medium text-white hover:bg-stone-700">Add Field</button>
            </form>

            <p class="mt-3 text-xs text-stone-500">
                This screen is for the Institute only; collaborators never see it.
                <strong>Order</strong> sets the position in lists.
                <strong>Offered</strong> means collaborators can choose the term. Untick it to stop
                offering a term to anyone new; people who already hold it keep it, so nothing is lost.
                The two numbers are how many Specialties sit under the Field, and how many
                collaborators currently hold it.
            </p>
            <div class="mt-3 hidden gap-2 px-3 text-xs font-medium text-stone-500 sm:grid sm:grid-cols-12">
                <span class="sm:col-span-1">Order</span>
                <span class="sm:col-span-3">Field</span>
                <span class="sm:col-span-5">Distinguishing line</span>
                <span class="sm:col-span-1">Offered</span>
                <span class="sm:col-span-1">Specialties · People</span>
            </div>
            <ul class="mt-1 divide-y divide-stone-100 rounded-lg border border-stone-200 bg-white">
                @foreach ($fields as $field)
                    <li class="p-3">
                        <form method="POST" action="{{ route('admin.taxonomy.fields.update', $field) }}" class="grid gap-2 sm:grid-cols-12 sm:items-center">
                            @csrf
                            @method('PUT')
                            <input type="number" name="sort_order" value="{{ $field->sort_order }}" min="0" max="9999"
                                   class="rounded-md border-stone-300 text-xs shadow-sm sm:col-span-1" title="Order">
                            <input type="text" name="name" value="{{ $field->name }}" required maxlength="100"
                                   class="rounded-md border-stone-300 text-sm shadow-sm sm:col-span-3">
                            <input type="text" name="descriptor" value="{{ $field->descriptor }}" maxlength="300"
                                   placeholder="Distinguishing line"
                                   class="rounded-md border-stone-300 text-sm shadow-sm sm:col-span-5">
                            <label class="flex items-center gap-1 text-xs text-stone-600 sm:col-span-1">
                                <input type="checkbox" name="active" value="1" @checked($field->active) class="rounded border-stone-300"> Offered
                            </label>
                            <span class="text-xs text-stone-500 sm:col-span-1" title="Specialties · collaborators">
                                {{ $field->specialties_count }} · {{ $field->profiles_count }}
                            </span>
                            <button type="submit" class="rounded-md border border-stone-300 px-2 py-1 text-xs hover:bg-stone-50 sm:col-span-1">Save</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </section>

        {{-- ================================================================ --}}
        <section class="mt-12">
            <h2 class="text-lg font-medium text-stone-800">Specialties <span class="text-sm font-normal text-stone-500">· {{ $specialties->count() }}</span></h2>

            <form method="POST" action="{{ route('admin.taxonomy.specialties.store') }}" class="mt-3 grid gap-2 rounded-md border border-dashed border-stone-300 bg-white p-3 sm:grid-cols-6">
                @csrf
                <input type="text" name="name" required maxlength="100" placeholder="New Specialty"
                       class="rounded-md border-stone-300 text-sm shadow-sm sm:col-span-2">
                <select name="field_ids[]" multiple size="3" class="rounded-md border-stone-300 text-sm shadow-sm sm:col-span-3"
                        aria-label="Fields it is recognizable within, for search">
                    @foreach ($allFields as $f)
                        <option value="{{ $f->id }}">{{ $f->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-md bg-stone-800 px-3 py-2 text-sm font-medium text-white hover:bg-stone-700">Add Specialty</button>
            </form>

            <div class="mt-3 hidden gap-2 px-3 text-xs font-medium text-stone-500 sm:grid sm:grid-cols-12">
                <span class="sm:col-span-4">Specialty</span>
                <span class="sm:col-span-5">Fields it is found under, for search</span>
                <span class="sm:col-span-1">Offered</span>
                <span class="sm:col-span-1">People</span>
            </div>
            <ul class="mt-1 divide-y divide-stone-100 rounded-lg border border-stone-200 bg-white">
                @foreach ($specialties as $specialty)
                    <li class="p-3">
                        <form method="POST" action="{{ route('admin.taxonomy.specialties.update', $specialty) }}" class="grid gap-2 sm:grid-cols-12 sm:items-start">
                            @csrf
                            @method('PUT')
                            <input type="text" name="name" value="{{ $specialty->name }}" required maxlength="100"
                                   class="rounded-md border-stone-300 text-sm shadow-sm sm:col-span-4">
                            <select name="field_ids[]" multiple size="2" class="rounded-md border-stone-300 text-xs shadow-sm sm:col-span-5"
                                    aria-label="Fields it is recognizable within">
                                @foreach ($allFields as $f)
                                    <option value="{{ $f->id }}" @selected($specialty->disciplines->contains('id', $f->id))>{{ $f->name }}</option>
                                @endforeach
                            </select>
                            <label class="flex items-center gap-1 text-xs text-stone-600 sm:col-span-1">
                                <input type="checkbox" name="active" value="1" @checked($specialty->active) class="rounded border-stone-300"> Offered
                            </label>
                            <span class="text-xs text-stone-500 sm:col-span-1" title="Collaborators">{{ $specialty->profiles_count }}</span>
                            <button type="submit" class="rounded-md border border-stone-300 px-2 py-1 text-xs hover:bg-stone-50 sm:col-span-1">Save</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </section>

        {{-- ================================================================ --}}
        <section class="mt-12">
            <h2 class="text-lg font-medium text-stone-800">Aliases <span class="text-sm font-normal text-stone-500">· {{ $aliases->count() }}</span></h2>
            <p class="mt-1 text-xs text-stone-500">
                Alternative wording that leads to a controlled term. Never displayed on a profile; it only helps somebody find the right entry.
            </p>

            <form method="POST" action="{{ route('admin.taxonomy.aliases.store') }}" class="mt-3 grid gap-2 rounded-md border border-dashed border-stone-300 bg-white p-3 sm:grid-cols-6">
                @csrf
                <input type="text" name="term" required maxlength="100" placeholder="Alias, e.g. Physiotherapy"
                       class="rounded-md border-stone-300 text-sm shadow-sm sm:col-span-2">
                <select name="target" required class="rounded-md border-stone-300 text-sm shadow-sm sm:col-span-2">
                    <option value="">Leads to…</option>
                    <optgroup label="Fields">
                        @foreach ($allFields as $f)
                            <option value="field:{{ $f->id }}">{{ $f->name }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Specialties">
                        @foreach ($specialties as $s)
                            <option value="specialty:{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </optgroup>
                </select>
                <label class="flex items-center gap-1 text-xs text-stone-600">
                    <input type="checkbox" name="ambiguous" value="1" class="rounded border-stone-300"> Ambiguous
                </label>
                <button type="submit" class="rounded-md bg-stone-800 px-3 py-2 text-sm font-medium text-white hover:bg-stone-700">Add alias</button>
            </form>

            <ul class="mt-3 divide-y divide-stone-100 rounded-lg border border-stone-200 bg-white text-sm">
                @foreach ($aliases as $alias)
                    <li class="flex flex-wrap items-center justify-between gap-2 px-3 py-2">
                        <span>
                            <span class="text-stone-800">{{ $alias->term }}</span>
                            <span class="text-stone-400">→</span>
                            <span class="text-stone-700">{{ $alias->discipline?->name ?? $alias->expertiseArea?->name }}</span>
                            <span class="ml-1 rounded bg-stone-100 px-1.5 py-0.5 text-xs text-stone-600">{{ $alias->discipline ? 'Field' : 'Specialty' }}</span>
                            @if ($alias->ambiguous)
                                <span class="ml-1 text-xs text-amber-700">ambiguous</span>
                            @endif
                        </span>
                        <form method="POST" action="{{ route('admin.taxonomy.aliases.retire', $alias) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-stone-500 underline">Retire</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>
</x-app-layout>
