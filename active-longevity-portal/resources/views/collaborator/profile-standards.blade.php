{{-- The Collaborator Profile Standards, readable at any time. --}}
<x-app-layout>
    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
        <a href="{{ route('profile.edit') }}" class="text-sm underline underline-offset-2">Back to your profile</a>

        <h1 class="mt-6 text-2xl font-semibold text-stone-800">Collaborator Profile Standards</h1>
        <p class="mt-1 text-xs text-stone-500">Version {{ config('portal.profile_standards_version') }}</p>

        <div class="mt-6">
            @include('collaborator.partials.profile-standards-text')
        </div>
    </div>
</x-app-layout>
