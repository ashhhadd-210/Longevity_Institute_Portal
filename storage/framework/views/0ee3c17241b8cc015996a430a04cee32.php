<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($title ?? config('app.name')); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="min-h-screen bg-stone-100 text-stone-800 antialiased">
    <div class="mx-auto flex min-h-screen max-w-2xl flex-col px-4 py-10">
        <header class="mb-8">
            <p class="text-lg font-semibold tracking-tight">Active Longevity Institute</p>
            <p class="text-sm text-stone-500">Curated Collaboration Portal</p>
        </header>

        <main class="rounded-md border border-stone-200 bg-white p-8 shadow-sm">
            <?php echo e($slot); ?>

        </main>

        <footer class="mt-8 text-xs text-stone-400">
            <p>Membership of the collaborator community is by decision of the Institute.</p>
        </footer>
    </div>
</body>
</html>
<?php /**PATH C:\Users\Muhammad Ashhad\Downloads\project longevity\active-longevity-portal\resources\views/components/public-layout.blade.php ENDPATH**/ ?>