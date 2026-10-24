<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ '' }}Collaborator Portal · Active Longevity Institute</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-100 text-stone-800 antialiased">
    <header class="border-b border-stone-200 bg-white">
        <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-3 px-4 py-3">
            <x-portal-mark />
            <div class="flex flex-wrap items-center gap-4 text-sm">
                @can('platform.admin.access')
                    <a href="{{ route('admin.queue.index') }}"
                       class="{{ request()->routeIs('admin.queue.*', 'admin.cases.*') ? 'font-medium text-stone-800' : 'text-stone-600' }} underline-offset-2 hover:underline">
                        Queue
                    </a>
                    <a href="{{ route('admin.vocabulary.index') }}"
                       class="{{ request()->routeIs('admin.vocabulary.*') ? 'font-medium text-stone-800' : 'text-stone-600' }} underline-offset-2 hover:underline">
                        Proposals
                    </a>
                    <a href="{{ route('admin.collaborators.index') }}"
                       class="{{ request()->routeIs('admin.collaborators.*') ? 'font-medium text-stone-800' : 'text-stone-600' }} underline-offset-2 hover:underline">
                        Collaborators
                    </a>
                    <a href="{{ route('admin.taxonomy.index') }}"
                       class="{{ request()->routeIs('admin.taxonomy.*') ? 'font-medium text-stone-800' : 'text-stone-600' }} underline-offset-2 hover:underline">
                        Registers
                    </a>
                    <a href="{{ route('admin.dashboard') }}"
                       class="{{ request()->routeIs('admin.dashboard') ? 'font-medium text-stone-800' : 'text-stone-600' }} underline-offset-2 hover:underline">
                        Administration
                    </a>
                @endcan
                @if (auth()->user()->collaborator)
                    <a href="{{ route('directory.index') }}"
                       class="{{ request()->routeIs('directory.*', 'profile.show') ? 'font-medium text-stone-800' : 'text-stone-600' }} underline-offset-2 hover:underline">
                        Directory
                    </a>
                    <a href="{{ route('profile.edit') }}"
                       class="{{ request()->routeIs('profile.edit', 'profile.classification.*') ? 'font-medium text-stone-800' : 'text-stone-600' }} underline-offset-2 hover:underline">
                        Your profile
                    </a>
                    <a href="{{ route('referrals.create') }}"
                       class="{{ request()->routeIs('referrals.*') ? 'font-medium text-stone-800' : 'text-stone-600' }} underline-offset-2 hover:underline">
                        Recommend
                    </a>
                @endif
                <span class="text-stone-600">{{ auth()->user()->fullName() }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-stone-500 underline-offset-2 hover:underline">Sign out</button>
                </form>
            </div>
        </div>
    </header>
    <main class="mx-auto max-w-5xl px-4 py-8">
        {{ $slot }}
    </main>
    <footer class="mx-auto max-w-5xl px-4 pb-8 pt-4 text-xs text-stone-500">
        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-stone-200 pt-4">
            <span>Active Longevity Institute</span>
            <a href="{{ route('feedback.create', ['from' => url()->current()]) }}" class="underline underline-offset-2 hover:text-stone-700">
                Tell the Institute what you think
            </a>
        </div>
    </footer>
</body>
</html>
