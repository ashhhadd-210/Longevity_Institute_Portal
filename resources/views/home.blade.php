<x-app-layout>
    <div class="mx-auto max-w-xl rounded-md border border-stone-200 bg-white p-8">
        <h1 class="mb-2 text-lg font-semibold">Welcome, {{ auth()->user()->first_name }}</h1>
        <p class="text-sm text-stone-600">
            You are signed in to the Institute Collaborator Portal. Collaborator
            profiles and the community directory open in a forthcoming release.
        </p>
    </div>
</x-app-layout>
