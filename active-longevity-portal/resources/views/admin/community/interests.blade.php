{{--
    Interests one collaborator has registered in working with another. The
    Institute sees every one and, at Alpha, passes it on by hand.
--}}
<x-app-layout>
    <div class="mx-auto max-w-4xl">
        <h1 class="text-2xl font-semibold text-stone-800">Collaboration interests</h1>
        <p class="mt-1 text-sm text-stone-600">
            One collaborator would like to work with another. Nothing has been sent between them.
            To pass it on, use "Introduce them by email": it opens your own email program with both
            addresses and a short draft you can edit before sending.
        </p>

        @if ($interests->isEmpty())
            <div class="mt-8 rounded-lg border border-stone-200 bg-white p-6 text-sm text-stone-600">
                Nobody has registered an interest yet.
            </div>
        @else
            <ul class="mt-6 divide-y divide-stone-100 rounded-lg border border-stone-200 bg-white">
                @foreach ($interests as $interest)
                    <li class="p-4">
                        <p class="text-sm">
                            <a href="{{ route('profile.show', $interest->from) }}" class="font-medium text-stone-800 underline underline-offset-2">{{ $interest->from?->collaborator?->user?->fullName() }}</a>
                            <span class="text-stone-500">would like to work with</span>
                            <a href="{{ route('profile.show', $interest->to) }}" class="font-medium text-stone-800 underline underline-offset-2">{{ $interest->to?->collaborator?->user?->fullName() }}</a>
                        </p>
                        @if (filled($interest->note))
                            <p class="mt-1 text-sm text-stone-600">"{{ $interest->note }}"</p>
                        @endif
                        @php
                            $from = $interest->from?->collaborator?->user;
                            $to = $interest->to?->collaborator?->user;
                            $fromName = $from?->preferred_name ?: $from?->first_name;
                            $toName = $to?->preferred_name ?: $to?->first_name;
                            $subject = 'An introduction from the Active Longevity Institute';
                            $body = "Dear {$toName} and {$fromName},\n\n{$fromName} has expressed an interest in working with {$toName} through the Institute's Collaborator Portal"
                                .(filled($interest->note) ? ", and wrote: \"{$interest->note}\"" : '.')
                                ."\n\nI am delighted to introduce you to one another and will leave you to take the conversation forward.\n\nWith best regards,\n";
                            $mailto = 'mailto:'.rawurlencode((string) $to?->email).','.rawurlencode((string) $from?->email)
                                .'?subject='.rawurlencode($subject).'&body='.rawurlencode($body);
                        @endphp
                        <p class="mt-2 text-xs text-stone-600">
                            {{ $from?->fullName() }}: <a href="mailto:{{ $from?->email }}" class="underline">{{ $from?->email }}</a><br>
                            {{ $to?->fullName() }}: <a href="mailto:{{ $to?->email }}" class="underline">{{ $to?->email }}</a>
                        </p>
                        <div class="mt-2 flex flex-wrap items-center gap-3">
                            <a href="{{ $mailto }}" class="rounded-md bg-stone-800 px-3 py-1.5 text-xs font-medium text-white hover:bg-stone-700">Introduce them by email</a>
                            <span class="text-xs text-stone-500">{{ $interest->created_at?->format('F j, Y, g:i a') }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>
            <div class="mt-4">{{ $interests->links() }}</div>
        @endif
    </div>
</x-app-layout>
