{{--
    What collaborators have told the Institute about the Portal, newest first,
    with the page each was on when they said it.
--}}
<x-app-layout>
    <div class="mx-auto max-w-4xl">
        <h1 class="text-2xl font-semibold text-stone-800">Feedback</h1>
        <p class="mt-1 text-sm text-stone-600">
            What collaborators have said about the Portal. At Alpha this is the most useful
            information the Institute has.
        </p>

        @if ($notes->isEmpty())
            <div class="mt-8 rounded-lg border border-stone-200 bg-white p-6 text-sm text-stone-600">
                Nothing yet. The link is in the footer of every page.
            </div>
        @else
            <ul class="mt-6 divide-y divide-stone-100 rounded-lg border border-stone-200 bg-white">
                @foreach ($notes as $note)
                    <li class="p-4">
                        <p class="whitespace-pre-line text-sm text-stone-800">{{ $note->message }}</p>
                        <p class="mt-2 text-xs text-stone-500">
                            {{ $note->user?->fullName() }}
                            · {{ $note->created_at?->format('j F Y, H:i') }}
                            @if (filled($note->page))
                                · on <span class="font-mono">{{ \Illuminate\Support\Str::after($note->page, '://') }}</span>
                            @endif
                        </p>
                    </li>
                @endforeach
            </ul>
            <div class="mt-4">{{ $notes->links() }}</div>
        @endif
    </div>
</x-app-layout>
