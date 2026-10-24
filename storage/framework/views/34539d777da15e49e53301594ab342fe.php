<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e(config('app.name')); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="min-h-screen bg-stone-100 text-stone-800 antialiased">
    <header class="border-b border-stone-200 bg-white">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-3">
            <div>
                <p class="font-semibold tracking-tight">Active Longevity Institute</p>
                <p class="text-xs text-stone-500">Collaborator Portal</p>
            </div>
            <div class="flex items-center gap-4 text-sm">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('platform.admin.access')): ?>
                    <a href="<?php echo e(route('admin.queue.index')); ?>"
                       class="<?php echo e(request()->routeIs('admin.queue.*', 'admin.cases.*') ? 'font-medium text-stone-800' : 'text-stone-600'); ?> underline-offset-2 hover:underline">
                        Queue
                    </a>
                    <a href="<?php echo e(route('admin.dashboard')); ?>"
                       class="<?php echo e(request()->routeIs('admin.dashboard') ? 'font-medium text-stone-800' : 'text-stone-600'); ?> underline-offset-2 hover:underline">
                        Administration
                    </a>
                <?php endif; ?>
                <?php if(auth()->user()->collaborator): ?>
                    <a href="<?php echo e(route('referrals.create')); ?>"
                       class="<?php echo e(request()->routeIs('referrals.*') ? 'font-medium text-stone-800' : 'text-stone-600'); ?> underline-offset-2 hover:underline">
                        Recommend
                    </a>
                <?php endif; ?>
                <span class="text-stone-600"><?php echo e(auth()->user()->fullName()); ?></span>
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="text-stone-500 underline-offset-2 hover:underline">Sign out</button>
                </form>
            </div>
        </div>
    </header>
    <main class="mx-auto max-w-5xl px-4 py-8">
        <?php echo e($slot); ?>

    </main>
</body>
</html>
<?php /**PATH C:\Users\Muhammad Ashhad\Downloads\project longevity\active-longevity-portal\resources\views/components/app-layout.blade.php ENDPATH**/ ?>