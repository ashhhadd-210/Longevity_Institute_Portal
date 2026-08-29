<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-100 text-stone-800 antialiased">
    <header class="border-b border-stone-200 bg-white">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-3">
            <div>
                <p class="font-semibold tracking-tight">Active Longevity Institute</p>
                <p class="text-xs text-stone-500">Collaborator Portal</p>
            </div>
            <div class="flex items-center gap-4 text-sm">
                @can('platform.admin.access')
                    <a href="{{ route('admin.queue.index') }}"
                       class="{{ request()->routeIs('admin.queue.*', 'admin.cases.*') ? 'font-medium text-stone-800' : 'text-stone-600' }} underline-offset-2 hover:underline">
                        Queue
                    </a>
                    <a href="{{ route('admin.dashboard') }}"
                       class="{{ request()->routeIs('admin.dashboard') ? 'font-medium text-stone-800' : 'text-stone-600' }} underline-offset-2 hover:underline">
                        Administration
                    </a>
                @endcan
                @if (auth()->user()->collaborator)
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
</body>
</html>
