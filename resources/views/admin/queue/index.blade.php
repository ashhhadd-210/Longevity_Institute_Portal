{{--
    Everything waiting for the Institute, oldest first, so that nobody waits
    longest merely because they arrived by a different route.

    Cases that have been set aside stay in this list. A pause is not a disposal,
    and a queue that hides paused work is a queue that loses it.
--}}
<x-app-layout>
    <div class="flex items-baseline justify-between">
        <div>
            <h1 class="text-xl font-semibold tracking-tight">Admission queue</h1>
            <p class="mt-1 text-sm text-stone-500">
                {{ $openCount }} {{ \Illuminate\Support\Str::plural('case', $openCount) }} open
            </p>
        </div>

        <a href="{{ route('admin.invitations.create') }}"
           class="rounded-md bg-stone-800 px-3 py-2 text-sm font-medium text-white hover:bg-stone-700">
            Invite someone directly
        </a>
    </div>

    @if (session('status'))
        <div class="mt-6 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <nav class="mt-6 flex gap-4 border-b border-stone-200 text-sm">
        @foreach (['open' => 'Open', 'decided' => 'Decided', 'all' => 'Everything'] as $key => $label)
            <a href="{{ route('admin.queue.index', ['show' => $key]) }}"
               class="-mb-px border-b-2 px-1 pb-2 {{ $show === $key ? 'border-stone-800 font-medium text-stone-800' : 'border-transparent text-stone-500 hover:text-stone-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </nav>

    @if ($items->isEmpty())
        <p class="mt-8 text-sm text-stone-500">Nothing here at the moment.</p>
    @else
        <div class="mt-6 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-stone-200 text-xs uppercase tracking-wide text-stone-500">
                    <tr>
                        <th class="py-2 pr-4 font-medium">Name</th>
                        <th class="py-2 pr-4 font-medium">How they arrived</th>
                        <th class="py-2 pr-4 font-medium">Status</th>
                        <th class="py-2 pr-4 font-medium">Waiting since</th>
                        <th class="py-2 font-medium"><span class="sr-only">Open</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($items as $row)
                        @php($subject = $row['subject'])
                        <tr class="align-top">
                            <td class="py-3 pr-4 font-medium text-stone-800">{{ $row['name'] }}</td>
                            <td class="py-3 pr-4 text-stone-600">
                                @if ($row['kind'] === 'referral')
                                    Referred by {{ $subject->referrer?->user?->fullName() ?? 'a collaborator' }}
                                @else
                                    Applied through the website
                                @endif
                            </td>
                            <td class="py-3 pr-4">
                                <span class="rounded-full bg-stone-100 px-2 py-0.5 text-xs text-stone-700">
                                    {{ $subject->status->label() }}
                                </span>
                            </td>
                            <td class="py-3 pr-4 text-stone-500">{{ $row['arrived']->diffForHumans() }}</td>
                            <td class="py-3 text-right">
                                @if ($subject->candidate_case_id)
                                    <a href="{{ route('admin.cases.show', $subject->candidate_case_id) }}"
                                       class="underline underline-offset-2">Open</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-app-layout>
