<x-app-layout>
    <div class="mb-6 flex items-baseline justify-between">
        <h1 class="text-lg font-semibold">Administration</h1>
        <p class="text-sm text-stone-500">Admission Release, Milestone 2</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <a href="{{ route('admin.queue.index') }}"
           class="rounded-md border border-stone-200 bg-white p-5 hover:border-stone-300">
            <p class="text-sm text-stone-500">Open applications</p>
            <p class="mt-1 text-2xl font-semibold">{{ $openApplications }}</p>
        </a>
        <a href="{{ route('admin.queue.index') }}"
           class="rounded-md border border-stone-200 bg-white p-5 hover:border-stone-300">
            <p class="text-sm text-stone-500">Open referrals</p>
            <p class="mt-1 text-2xl font-semibold">{{ $openReferrals }}</p>
        </a>
        <div class="rounded-md border border-stone-200 bg-white p-5">
            <p class="text-sm text-stone-500">Outstanding invitations</p>
            <p class="mt-1 text-2xl font-semibold">{{ $outstandingInvitations }}</p>
        </div>
        <div class="rounded-md border border-stone-200 bg-white p-5">
            <p class="text-sm text-stone-500">Collaborators</p>
            <p class="mt-1 text-2xl font-semibold">{{ $collaboratorCount }}</p>
        </div>
    </div>

    <div class="mt-8 flex flex-wrap gap-3">
        <a href="{{ route('admin.queue.index') }}"
           class="rounded-md bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
            Open the admission queue
        </a>
        <a href="{{ route('admin.invitations.create') }}"
           class="rounded-md border border-stone-300 bg-white px-4 py-2 text-sm font-medium hover:bg-stone-50">
            Invite someone directly
        </a>
    </div>

    <p class="mt-8 text-sm text-stone-500">
        Collaborator profiles, the directory and search arrive with Milestone 3 on
        this same foundation.
    </p>
</x-app-layout>
