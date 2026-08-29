<x-app-layout>
    <div class="mx-auto max-w-xl">
        @if (session('just_admitted'))
            <div class="mb-6 rounded-md border border-green-200 bg-green-50 p-5">
                <p class="font-medium text-green-900">Welcome to the Institute</p>
                <p class="mt-1 text-sm text-green-800">
                    Your registration is complete and you are now a collaborator.
                </p>
            </div>
        @endif

        <div class="rounded-md border border-stone-200 bg-white p-8">
            {{-- Greeted by the same rule that governs every message the Portal
                 sends, so the Portal never addresses one person two ways. --}}
            <h1 class="mb-2 text-lg font-semibold">
                {{ \App\Support\Salutation::greeting(auth()->user(), 'Welcome') }}
            </h1>

            <p class="text-sm text-stone-600">
                You are signed in to the Institute's Curated Collaboration Portal.
                Collaborator profiles and the community directory open in a
                forthcoming release.
            </p>

            @if (auth()->user()->collaborator)
                <div class="mt-6 border-t border-stone-100 pt-6">
                    <p class="text-sm font-medium">Know someone who belongs here?</p>
                    <p class="mt-1 text-sm text-stone-600">
                        The Institute grows by recommendation. Nothing is sent to the person
                        you name unless the Institute decides to invite them.
                    </p>
                    <a href="{{ route('referrals.create') }}"
                       class="mt-4 inline-block rounded-md bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
                        Recommend a professional
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
