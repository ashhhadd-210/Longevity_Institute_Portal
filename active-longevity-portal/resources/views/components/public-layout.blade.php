<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($title) ? $title.' · ' : '' }}Collaborator Portal · Active Longevity Institute</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-100 text-stone-800 antialiased">
    <div class="mx-auto flex min-h-screen max-w-2xl flex-col px-4 py-10">
        <header class="mb-8">
            <x-portal-mark size="lg" />
        </header>

        <main class="rounded-md border border-stone-200 bg-white p-8 shadow-sm">
            {{ $slot }}
        </main>

        <footer class="mt-8 text-xs text-stone-400">
            <p>Active Longevity Institute · Admission to the collaborator community is by decision of the Institute.</p>
        </footer>
    </div>
</body>
</html>
