{{--
    A collaborator's own account of their work.

    Each section saves on its own, because these are filled in over several
    sittings and often on different devices. Nothing is lost by stopping
    halfway, and there is no wizard to get trapped in.

    Sectioned rather than presented as one enormous form, which matters most on
    a phone.
--}}
<x-app-layout>
    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6">

        @if (session('status'))
            <div class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                {{ session('status') }}
            </div>
        @endif

        @if ($showStandards)
            <section class="mb-8 rounded-lg border border-stone-200 bg-stone-50 p-5">
                <h2 class="text-base font-medium text-stone-800">Collaborator Profile Standards</h2>
                <div class="mt-3">
                    @include('collaborator.partials.profile-standards-text')
                </div>
                <p class="mt-3 text-xs text-stone-500">
                    You will not be shown this again. It is always available from
                    <a href="{{ route('profile.standards') }}" class="underline">Profile Standards</a>
                    at the top of this page.
                </p>
            </section>
        @endif

        <div class="flex flex-wrap items-baseline justify-between gap-2">
            <h1 class="text-2xl font-semibold text-stone-800">Your profile</h1>
            <div class="flex gap-4 text-sm">
                <a href="{{ route('profile.standards') }}" class="underline underline-offset-2">Profile Standards</a>
                <a href="{{ route('profile.show', $profile) }}" class="underline underline-offset-2">Preview my profile</a>
            </div>
        </div>

        @if ($complete)
            <div class="mt-3 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                <p class="font-medium">Your profile is ready for Directory Review.</p>
                <p class="mt-1">
                    @if ($listed)
                        It now appears in the collaborator directory.
                    @else
                        It is not shown in the directory at the moment; that is the Institute's
                        decision, and you may ask them about it.
                    @endif
                    <a href="{{ route('profile.show', $profile) }}" class="underline">Preview my profile</a>
                    to see it as other collaborators will.
                </p>
            </div>
        @else
            <div class="mt-3 rounded-md border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700">
                <p class="font-medium">Not yet ready for the Directory</p>
                <p class="mt-1">Still needed, and nothing else is required:</p>
                <ul class="mt-2 space-y-1">
                    @foreach ($missing as $item)
                        <li>
                            <a href="#{{ $item['anchor'] }}" class="underline underline-offset-2">{{ $item['label'] }}</a>
                        </li>
                    @endforeach
                </ul>
                <p class="mt-2 text-xs text-stone-500">
                    Each part saves on its own, so you can stop at any time and return later by
                    signing in at this same address.
                </p>
            </div>
        @endif

        {{-- ------------------------------------------------------------- --}}
        <section id="name" class="mt-10 scroll-mt-6">
            <h2 class="text-lg font-medium text-stone-800">Your name</h2>
            <p class="mt-1 text-sm text-stone-600">
                As you are known professionally. An initial is fine wherever you use one,
                for example "P." with "Thomas" as your middle name for P. Thomas Smith, or
                "PT" for PT Smith.
            </p>
            <form method="POST" action="{{ route('profile.name') }}" class="mt-4 space-y-4">
                @csrf
                @method('PUT')
                <div class="grid gap-3 sm:grid-cols-3">
                    <div>
                        <label for="first_name" class="block text-sm font-medium">First name or initial</label>
                        <input id="first_name" name="first_name" type="text" required maxlength="{{ \App\Support\FieldLimits::NAME }}"
                               value="{{ old('first_name', $user?->first_name) }}"
                               class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
                    </div>
                    <div>
                        <label for="middle_name" class="block text-sm font-medium">Middle name or initial <span class="font-normal text-stone-400">(optional)</span></label>
                        <input id="middle_name" name="middle_name" type="text" maxlength="{{ \App\Support\FieldLimits::NAME }}"
                               value="{{ old('middle_name', $user?->middle_name) }}"
                               class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium">Family name</label>
                        <input id="last_name" name="last_name" type="text" required maxlength="{{ \App\Support\FieldLimits::NAME }}"
                               value="{{ old('last_name', $user?->last_name) }}"
                               class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <div>
                        <label for="name_suffix" class="block text-sm font-medium">Suffix <span class="font-normal text-stone-400">(optional)</span></label>
                        <input id="name_suffix" name="name_suffix" type="text" maxlength="{{ \App\Support\FieldLimits::NAME_SUFFIX }}"
                               placeholder="Jr., Sr., III"
                               value="{{ old('name_suffix', $user?->name_suffix) }}"
                               class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="preferred_name" class="block text-sm font-medium">Name I go by <span class="font-normal text-stone-400">(optional)</span></label>
                        <input id="preferred_name" name="preferred_name" type="text" maxlength="{{ \App\Support\FieldLimits::NAME }}"
                               placeholder="For example, Thomas"
                               value="{{ old('preferred_name', $user?->preferred_name) }}"
                               class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
                        <p class="mt-1 text-xs text-stone-500">How you would like to be addressed when someone speaks or writes to you.</p>
                    </div>
                </div>
                @foreach (['first_name', 'middle_name', 'last_name', 'name_suffix', 'preferred_name'] as $nameField)
                    @error($nameField)<p class="text-sm text-red-700">{{ $message }}</p>@enderror
                @endforeach
                <button type="submit" class="rounded-md bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">Save name</button>
            </form>
        </section>

        {{-- ------------------------------------------------------------- --}}
        <section id="photograph" class="mt-10 flex scroll-mt-6 flex-col gap-6 border-t border-stone-200 pt-8 sm:flex-row">
            <div class="flex-none">
                @if (filled($profile->photo_path))
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk(config('portal.photo_disk', 'public'))->url($profile->photo_path) }}"
                         alt="Your photograph"
                         class="h-40 w-30 rounded-md border border-stone-200 object-cover" style="width:7.5rem">
                @else
                    <div class="flex h-40 items-center justify-center rounded-md border border-dashed border-stone-300 bg-stone-50 text-xs text-stone-400" style="width:7.5rem">
                        No photograph
                    </div>
                @endif
            </div>

            <div class="min-w-0 flex-1">
                <h2 class="text-lg font-medium text-stone-800">Your photograph</h2>
                <p class="mt-1 text-sm text-stone-600">
                    An individual professional image of you, ideally head and shoulders. No
                    logos, avatars, group photographs or organizational graphics. Cultural and
                    religious dress is entirely welcome.
                </p>
                <p class="mt-1 text-xs text-stone-500">
                    Needed before your profile appears in the directory. It is shown as a portrait
                    rectangle, so anything wider is cropped to the center. JPEG, PNG or WebP, up to 8 MB.
                </p>

                <form method="POST" action="{{ route('profile.photo') }}" enctype="multipart/form-data" class="mt-3 flex flex-wrap items-center gap-3">
                    @csrf
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required
                           class="text-sm text-stone-700 file:mr-3 file:rounded-md file:border-0 file:bg-stone-800 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-white hover:file:bg-stone-700">
                    <button type="submit" class="rounded-md border border-stone-300 px-3 py-1.5 text-sm font-medium hover:bg-white">
                        Upload
                    </button>
                </form>

                @error('photo')
                    <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                @enderror

                @if (filled($profile->photo_path))
                    <form method="POST" action="{{ route('profile.photo.destroy') }}" class="mt-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-stone-500 underline">Remove photograph</button>
                    </form>
                @endif
            </div>
        </section>

        {{-- ------------------------------------------------------------- --}}
        <section class="mt-10 border-t border-stone-200 pt-8">
            <h2 class="text-lg font-medium text-stone-800">Who you are professionally</h2>

            <form method="POST" action="{{ route('profile.details') }}" class="mt-4 space-y-5">
                @csrf
                @method('PUT')

                <div id="identity" class="scroll-mt-6">
                    <label for="professional_title" class="block text-sm font-medium">Professional Identity</label>
                    <p class="mt-0.5 text-sm text-stone-600">
                        How would you like your professional identity to appear beneath your name in
                        the Collaborator Directory?
                    </p>
                    <input id="professional_title" name="professional_title" type="text"
                           maxlength="{{ \App\Support\FieldLimits::TITLE }}"
                           value="{{ old('professional_title', $profile->professional_title) }}"
                           class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
                    <p class="mt-1 text-xs text-stone-500">
                        Up to {{ \App\Support\FieldLimits::TITLE }} characters. Credentials, Fields,
                        Specialties, interests and achievements each have their own place on your
                        profile, so they do not belong here.
                    </p>
                    @error('professional_title')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="one_line_summary" id="about-your-work" class="block scroll-mt-6 text-sm font-medium">About Your Work</label>
                    <p class="mt-0.5 text-sm text-stone-600">
                        In one or two sentences, what would you most like other Institute collaborators
                        to understand about your professional work? Please write for collaborators
                        outside your own field. Use clear language and avoid specialized terminology
                        where a more widely understandable expression is available.
                    </p>
                    <textarea id="one_line_summary" name="one_line_summary" rows="3"
                              maxlength="{{ \App\Support\FieldLimits::ABOUT_YOUR_WORK }}"
                              class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">{{ old('one_line_summary', $profile->one_line_summary) }}</textarea>
                    <p class="mt-1 text-xs text-stone-500">Up to {{ \App\Support\FieldLimits::ABOUT_YOUR_WORK }} characters.</p>
                    @error('one_line_summary')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="about" class="block text-sm font-medium">
                        Additional Information <span class="font-normal text-stone-400">(optional)</span>
                    </label>
                    <p class="mt-0.5 text-sm text-stone-600">
                        Include other information that is relevant to your career and allows other
                        collaborators to have a more complete understanding of your areas of
                        specialization. Field-specific terminology is appropriate here.
                    </p>
                    <textarea id="about" name="about" rows="6"
                              maxlength="{{ \App\Support\FieldLimits::ABOUT }}"
                              class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">{{ old('about', $profile->about) }}</textarea>
                    <p class="mt-1 text-xs text-stone-500">
                        Up to {{ \App\Support\FieldLimits::ABOUT }} characters.
                    </p>
                    @error('about')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div id="country" class="scroll-mt-6">
                        <label for="country_code" class="block text-sm font-medium">Country</label>
                        <select id="country_code" name="country_code" class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
                            <option value="">Choose a country</option>
                            @foreach ($countries as $code => $countryName)
                                <option value="{{ $code }}" @selected(old('country_code', $profile->country_code) === $code)>{{ $countryName }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-stone-500">Shown to collaborators.</p>
                    </div>
                    <div>
                        <label for="city" class="block text-sm font-medium">City</label>
                        <input id="city" name="city" type="text" maxlength="200"
                               value="{{ old('city', $profile->city) }}"
                               class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
                    </div>
                    <div>
                        <label for="state_region" class="block text-sm font-medium">State or region</label>
                        <input id="state_region" name="state_region" type="text" maxlength="200"
                               value="{{ old('state_region', $profile->state_region) }}"
                               class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
                    </div>
                </div>
                <p class="text-xs text-stone-500">
                    Your city and region are seen by the Institute only. They never appear on
                    your profile or in the directory.
                </p>

                <label class="flex items-start gap-2 text-sm">
                    <input type="checkbox" name="accepts_collaboration_interest" value="1"
                           @checked(old('accepts_collaboration_interest', $profile->accepts_collaboration_interest ?? true))
                           class="mt-0.5 rounded border-stone-300">
                    <span>
                        <span class="font-medium">Open to approaches</span><br>
                        <span class="text-xs text-stone-500">
                            Other collaborators may register an interest in working with you. The
                            Institute sees it and passes it on. Untick this and nobody can.
                        </span>
                    </span>
                </label>

                <button type="submit"
                        class="rounded-md bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
                    Save
                </button>
            </form>
        </section>

        {{-- ------------------------------------------------------------- --}}
        <section class="mt-12 border-t border-stone-200 pt-8">
            <h2 class="text-lg font-medium text-stone-800">
                Your current positions <span class="font-normal text-stone-400">(up to {{ $maxPositions }})</span>
            </h2>
            <p class="mt-1 text-sm text-stone-600">
                Some people hold more than one appointment at once. Only the title is needed; an
                organization is optional, because not every legitimate role sits inside one. Leave
                a title blank to remove that position.
            </p>

            <form method="POST" action="{{ route('profile.positions') }}" class="mt-4 space-y-4">
                @csrf
                @method('PUT')

                @for ($i = 0; $i < $maxPositions; $i++)
                    @php $pos = $positions[$i]->payload ?? []; @endphp
                    <fieldset class="rounded-md border border-stone-200 bg-white p-3">
                        <legend class="px-1 text-xs text-stone-500">Position {{ $i + 1 }}</legend>
                        <div class="grid gap-2">
                            <input type="text" name="positions[{{ $i }}][title]"
                                   maxlength="{{ \App\Support\FieldLimits::TITLE }}"
                                   placeholder="Title, in full"
                                   value="{{ old("positions.$i.title", $pos['title'] ?? '') }}"
                                   class="rounded-md border-stone-300 text-sm shadow-sm">
                            <div class="grid gap-2 sm:grid-cols-2">
                                <input type="text" name="positions[{{ $i }}][organisation]"
                                       maxlength="{{ \App\Support\FieldLimits::ORGANISATION }}"
                                       placeholder="Organization (optional)"
                                       value="{{ old("positions.$i.organisation", $pos['organisation'] ?? '') }}"
                                       class="rounded-md border-stone-300 text-sm shadow-sm">
                                <input type="text" name="positions[{{ $i }}][department]"
                                       maxlength="{{ \App\Support\FieldLimits::DEPARTMENT }}"
                                       placeholder="Department or division (optional)"
                                       value="{{ old("positions.$i.department", $pos['department'] ?? '') }}"
                                       class="rounded-md border-stone-300 text-sm shadow-sm">
                            </div>
                            <div class="grid gap-2 sm:grid-cols-2">
                                <select name="positions[{{ $i }}][country]" class="rounded-md border-stone-300 text-sm shadow-sm" aria-label="Country (optional)">
                                    <option value="">Country (optional)</option>
                                    @foreach ($countries as $code => $countryName)
                                        <option value="{{ $code }}" @selected(old("positions.$i.country", $pos['country'] ?? '') === $code)>{{ $countryName }}</option>
                                    @endforeach
                                </select>
                                <input type="number" name="positions[{{ $i }}][start_year]" min="1940" max="{{ now()->year + 1 }}"
                                       placeholder="Start year (optional)"
                                       value="{{ old("positions.$i.start_year", $pos['start_year'] ?? '') }}"
                                       class="rounded-md border-stone-300 text-sm shadow-sm">
                            </div>
                        </div>
                    </fieldset>
                @endfor

                <button type="submit"
                        class="rounded-md bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
                    Save positions
                </button>
            </form>
        </section>

        {{-- ------------------------------------------------------------- --}}
        <section id="fields" class="mt-12 scroll-mt-6 border-t border-stone-200 pt-8">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <h2 class="text-lg font-medium text-stone-800">Where your work belongs</h2>
                <a href="{{ route('profile.classification.edit') }}" class="text-sm underline underline-offset-2">
                    Change
                </a>
            </div>

            @if ($fields->isEmpty())
                <p class="mt-2 text-sm text-stone-600">
                    You have not chosen a Field yet. It is the one thing the directory needs.
                </p>
            @else
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($fields as $field)
                        <span class="rounded-full bg-stone-100 px-3 py-1 text-sm text-stone-800">{{ $field->name }}</span>
                    @endforeach
                    @foreach ($specialties as $specialty)
                        <span class="rounded-full border border-stone-200 px-3 py-1 text-sm text-stone-600">{{ $specialty->name }}</span>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ------------------------------------------------------------- --}}
        <section class="mt-12 border-t border-stone-200 pt-8">
            <h2 class="text-lg font-medium text-stone-800">
                Credentials after your name <span class="font-normal text-stone-400">(up to three)</span>
            </h2>
            <p class="mt-1 text-sm text-stone-600">
                Start typing the letters or the full name of the qualification. The same letters
                can mean different things in different countries, so the meaning is shown beside
                each one to help you choose the right entry.
            </p>

            <form method="POST" action="{{ route('profile.credentials') }}" class="mt-4">
                @csrf
                @method('PUT')

                <div data-credential-picker>
                    <input type="search" data-search autocomplete="off"
                           placeholder="Try PhD, MPH, FRCP, or public health"
                           class="w-full rounded-md border-stone-300 text-sm shadow-sm focus:border-stone-500 focus:ring-stone-500">
                    <ul data-results class="mt-2 hidden divide-y divide-stone-100 rounded-md border border-stone-200 bg-white"></ul>

                    <p class="mt-4 text-xs text-stone-500">Shown after your name in this order. Unused lines do not appear on your profile.</p>
                    <ol data-chosen class="mt-2 space-y-2">
                        @foreach ($credentials as $credential)
                            <li class="flex items-start justify-between gap-3 rounded-md border border-stone-200 bg-white px-3 py-2">
                                <div>
                                    <span class="text-sm font-medium text-stone-800">{{ $credential->acronym }}</span>
                                    <span class="ml-2 text-xs text-stone-500">{{ $credential->describe() }}</span>
                                </div>
                                <button type="button" data-remove class="text-xs text-stone-500 underline">Remove</button>
                                <input type="hidden" name="credential_ids[]" value="{{ $credential->id }}">
                            </li>
                        @endforeach
                    </ol>
                </div>

                @error('credential_ids')
                    <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                @enderror

                <button type="submit"
                        class="mt-4 rounded-md bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
                    Save credentials
                </button>
            </form>

            <div class="mt-6 rounded-lg border border-stone-200 bg-stone-50 p-4">
                <p class="text-sm font-medium text-stone-800">Cannot find yours?</p>
                <p class="mt-1 text-sm text-stone-600">
                    Tell us the letters and what they stand for. Your profile is saved either way, and
                    the credential will appear once the Institute has approved it.
                </p>

                @if (session('credential_near_matches'))
                    <div class="mt-3 rounded-md border border-amber-200 bg-amber-50 p-3 text-sm">
                        <p class="font-medium text-amber-900">Is it one of these? They are already in the library.</p>
                        <ul class="mt-1 text-amber-900">
                            @foreach (session('credential_near_matches') as $match)
                                <li>{{ $match['acronym'] }} — {{ $match['describe'] }}</li>
                            @endforeach
                        </ul>
                        <p class="mt-2 text-xs text-amber-800">If so, search for it above. If not, send your request again and it will go to the Institute.</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.credentials.propose') }}" class="mt-3 grid gap-2 sm:grid-cols-6">
                    @csrf
                    @if (session('credential_near_matches'))
                        <input type="hidden" name="confirmed" value="1">
                    @endif
                    <input type="text" name="acronym" required maxlength="40" placeholder="Letters"
                           value="{{ old('acronym') }}"
                           class="rounded-md border-stone-300 text-sm shadow-sm sm:col-span-1">
                    <input type="text" name="meaning" required maxlength="300" placeholder="What they stand for"
                           value="{{ old('meaning') }}"
                           class="rounded-md border-stone-300 text-sm shadow-sm sm:col-span-3">
                    <input type="text" name="jurisdiction" maxlength="120" placeholder="Country (optional)"
                           value="{{ old('jurisdiction') }}"
                           class="rounded-md border-stone-300 text-sm shadow-sm sm:col-span-1">
                    <button type="submit"
                            class="rounded-md border border-stone-300 px-3 py-2 text-sm font-medium hover:bg-white sm:col-span-1">
                        {{ session('credential_near_matches') ? 'Still request' : 'Request' }}
                    </button>
                </form>

                @if ($credentialProposals->isNotEmpty())
                    <ul class="mt-4 space-y-1 text-xs text-stone-600">
                        @foreach ($credentialProposals as $proposal)
                            <li>
                                <span class="font-medium text-stone-800">{{ $proposal->term }}</span>
                                — {{ $proposal->meaning }} — {{ $proposal->statusForCollaborator() }}
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>

        {{-- ------------------------------------------------------------- --}}
        <section class="mt-12 border-t border-stone-200 pt-8">
            <h2 class="text-lg font-medium text-stone-800">Links</h2>
            <p class="mt-1 text-sm text-stone-600">
                Up to {{ $maxLinks }}. Write a short label for each: the label is what people
                see and click, and the address itself is not printed. Leave a row blank to
                remove it.
            </p>

            <form method="POST" action="{{ route('profile.links') }}" class="mt-4 space-y-3">
                @csrf
                @method('PUT')

                @for ($i = 0; $i < $maxLinks; $i++)
                    @php $link = $links[$i] ?? null; @endphp
                    <div class="grid gap-2 sm:grid-cols-5">
                        <input type="text" name="links[{{ $i }}][label]"
                               maxlength="{{ \App\Support\FieldLimits::LINK_LABEL }}"
                               placeholder="Label, for example University page"
                               value="{{ old("links.$i.label", $link?->label) }}"
                               class="rounded-md border-stone-300 text-sm shadow-sm sm:col-span-2">
                        <input type="url" name="links[{{ $i }}][url]" maxlength="2048"
                               placeholder="https://"
                               value="{{ old("links.$i.url", $link?->url) }}"
                               class="rounded-md border-stone-300 text-sm shadow-sm sm:col-span-3">
                    </div>
                @endfor

                <x-form-errors />

                <button type="submit"
                        class="rounded-md bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
                    Save links
                </button>
            </form>
        </section>
    </div>

    {{--
        The credential picker. Progressive enhancement: without JavaScript the
        chosen credentials still save and the request form still works.
    --}}
    <script>
        (function () {
            var panel = document.querySelector('[data-credential-picker]');
            if (!panel) return;

            var input = panel.querySelector('[data-search]');
            var results = panel.querySelector('[data-results]');
            var chosen = panel.querySelector('[data-chosen]');
            var timer = null;
            var MAX = {{ \App\Services\Expertise\CredentialLibrary::MAX_DISPLAYED }};

            function count() { return chosen.querySelectorAll('input[name="credential_ids[]"]').length; }

            // Three numbered lines, like Links and Positions: the chosen
            // credentials first, then an empty line for each one still free.
            function paint() {
                chosen.querySelectorAll('[data-empty]').forEach(function (e) { e.remove(); });
                var filled = chosen.querySelectorAll('li:not([data-empty])');
                filled.forEach(function (li, i) {
                    var n = li.querySelector('[data-number]');
                    if (!n) {
                        n = document.createElement('span');
                        n.setAttribute('data-number', '');
                        n.className = 'mr-2 text-xs text-stone-400';
                        li.firstElementChild.prepend(n);
                    }
                    n.textContent = (i + 1) + '.';
                });
                for (var i = filled.length; i < MAX; i++) {
                    var empty = document.createElement('li');
                    empty.setAttribute('data-empty', '');
                    empty.className = 'rounded-md border border-dashed border-stone-200 px-3 py-2 text-xs text-stone-400';
                    empty.textContent = (i + 1) + '. ' + (i === filled.length ? 'Search above to add a credential' : 'Not used');
                    chosen.appendChild(empty);
                }
            }

            function add(item) {
                if (chosen.querySelector('input[value="' + item.id + '"]')) return;
                if (count() >= MAX) {
                    input.value = '';
                    results.classList.add('hidden');
                    input.placeholder = 'You already show three. Remove one to add another.';
                    return;
                }

                var li = document.createElement('li');
                li.className = 'flex items-start justify-between gap-3 rounded-md border border-stone-200 bg-white px-3 py-2';

                var left = document.createElement('div');
                var acr = document.createElement('span');
                acr.className = 'text-sm font-medium text-stone-800';
                acr.textContent = item.acronym;
                var desc = document.createElement('span');
                desc.className = 'ml-2 text-xs text-stone-500';
                desc.textContent = item.describe;
                left.appendChild(acr); left.appendChild(desc);

                var remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'text-xs text-stone-500 underline';
                remove.textContent = 'Remove';
                remove.addEventListener('click', function () { li.remove(); paint(); });

                var hidden = document.createElement('input');
                hidden.type = 'hidden'; hidden.name = 'credential_ids[]'; hidden.value = item.id;

                li.appendChild(left); li.appendChild(remove); li.appendChild(hidden);
                var firstEmpty = chosen.querySelector('[data-empty]');
                chosen.insertBefore(li, firstEmpty);
                paint();

                input.value = '';
                results.classList.add('hidden');
                results.innerHTML = '';
            }

            function draw(items) {
                results.innerHTML = '';
                if (!items.length) { results.classList.add('hidden'); return; }
                items.forEach(function (item) {
                    var li = document.createElement('li');
                    li.className = 'cursor-pointer px-3 py-2 hover:bg-stone-50';
                    li.tabIndex = 0; li.setAttribute('role', 'button');
                    var a = document.createElement('span');
                    a.className = 'text-sm font-medium text-stone-800';
                    a.textContent = item.acronym;
                    var d = document.createElement('span');
                    d.className = 'ml-2 text-xs text-stone-500';
                    d.textContent = item.describe;
                    li.appendChild(a); li.appendChild(d);
                    li.addEventListener('click', function () { add(item); });
                    li.addEventListener('keydown', function (e) {
                        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); add(item); }
                    });
                    results.appendChild(li);
                });
                results.classList.remove('hidden');
            }

            input.addEventListener('input', function () {
                window.clearTimeout(timer);
                var q = input.value.trim();
                if (q.length < 2) { results.classList.add('hidden'); return; }
                timer = window.setTimeout(function () {
                    fetch('{{ route('credentials.search') }}?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } })
                        .then(function (r) { return r.json(); })
                        .then(function (data) { draw(data.results || []); })
                        .catch(function () { results.classList.add('hidden'); });
                }, 180);
            });

            chosen.querySelectorAll('[data-remove]').forEach(function (b) {
                b.addEventListener('click', function () { b.closest('li').remove(); paint(); });
            });
            paint();
        })();
    </script>
</x-app-layout>
