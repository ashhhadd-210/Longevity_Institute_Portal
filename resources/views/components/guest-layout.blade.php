<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-100 text-stone-800 antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center px-4">
        <div class="mb-8 text-center">
            <p class="text-lg font-semibold tracking-tight">Active Longevity Institute</p>
            <p class="text-sm text-stone-500">Collaborator Portal</p>
        </div>
        <div class="w-full max-w-md rounded-md border border-stone-200 bg-white p-8 shadow-sm">
            {{ $slot }}
        </div>
        <p class="mt-8 text-xs text-stone-400">Access to this Portal is by invitation of the Institute.</p>
    </div>
</body>
</html>
