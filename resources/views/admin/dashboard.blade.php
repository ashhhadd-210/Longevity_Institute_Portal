<x-app-layout>
    <div class="mb-6 flex items-baseline justify-between">
        <h1 class="text-lg font-semibold">Administration</h1>
        <p class="text-sm text-stone-500">Foundation Release — Milestone 1</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-md border border-stone-200 bg-white p-5">
            <p class="text-sm text-stone-500">Collaborators</p>
            <p class="mt-1 text-2xl font-semibold">{{ $collaboratorCount }}</p>
        </div>
        <div class="rounded-md border border-stone-200 bg-white p-5">
            <p class="text-sm text-stone-500">Open applications</p>
            <p class="mt-1 text-2xl font-semibold">{{ $openApplications }}</p>
        </div>
        <div class="rounded-md border border-stone-200 bg-white p-5">
            <p class="text-sm text-stone-500">Open referrals</p>
            <p class="mt-1 text-2xl font-semibold">{{ $openReferrals }}</p>
        </div>
        <div class="rounded-md border border-stone-200 bg-white p-5">
            <p class="text-sm text-stone-500">Outstanding invitations</p>
            <p class="mt-1 text-2xl font-semibold">{{ $outstandingInvitations }}</p>
        </div>
    </div>

    <p class="mt-8 text-sm text-stone-500">
        The admission review queue, invitation management and collaborator
        administration arrive with Milestone 2 on this same foundation.
    </p>
</x-app-layout>
