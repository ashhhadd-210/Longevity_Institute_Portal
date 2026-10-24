<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e(''); ?>Collaborator Portal · Active Longevity Institute</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="min-h-screen bg-stone-100 text-stone-800 antialiased">
    <header class="border-b border-stone-200 bg-white">
        <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-3 px-4 py-3">
            <?php if (isset($component)) { $__componentOriginal2d0dd047bc9f587ee4fcbc3aad9dee65 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2d0dd047bc9f587ee4fcbc3aad9dee65 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.portal-mark','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('portal-mark'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2d0dd047bc9f587ee4fcbc3aad9dee65)): ?>
<?php $attributes = $__attributesOriginal2d0dd047bc9f587ee4fcbc3aad9dee65; ?>
<?php unset($__attributesOriginal2d0dd047bc9f587ee4fcbc3aad9dee65); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2d0dd047bc9f587ee4fcbc3aad9dee65)): ?>
<?php $component = $__componentOriginal2d0dd047bc9f587ee4fcbc3aad9dee65; ?>
<?php unset($__componentOriginal2d0dd047bc9f587ee4fcbc3aad9dee65); ?>
<?php endif; ?>
            <div class="flex flex-wrap items-center gap-4 text-sm">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('platform.admin.access')): ?>
                    <a href="<?php echo e(route('admin.queue.index')); ?>"
                       class="<?php echo e(request()->routeIs('admin.queue.*', 'admin.cases.*') ? 'font-medium text-stone-800' : 'text-stone-600'); ?> underline-offset-2 hover:underline">
                        Queue
                    </a>
                    <a href="<?php echo e(route('admin.vocabulary.index')); ?>"
                       class="<?php echo e(request()->routeIs('admin.vocabulary.*') ? 'font-medium text-stone-800' : 'text-stone-600'); ?> underline-offset-2 hover:underline">
                        Proposals
                    </a>
                    <a href="<?php echo e(route('admin.collaborators.index')); ?>"
                       class="<?php echo e(request()->routeIs('admin.collaborators.*') ? 'font-medium text-stone-800' : 'text-stone-600'); ?> underline-offset-2 hover:underline">
                        Collaborators
                    </a>
                    <a href="<?php echo e(route('admin.taxonomy.index')); ?>"
                       class="<?php echo e(request()->routeIs('admin.taxonomy.*') ? 'font-medium text-stone-800' : 'text-stone-600'); ?> underline-offset-2 hover:underline">
                        Registers
                    </a>
                    <a href="<?php echo e(route('admin.dashboard')); ?>"
                       class="<?php echo e(request()->routeIs('admin.dashboard') ? 'font-medium text-stone-800' : 'text-stone-600'); ?> underline-offset-2 hover:underline">
                        Administration
                    </a>
                <?php endif; ?>
                <?php if(auth()->user()->collaborator): ?>
                    <a href="<?php echo e(route('directory.index')); ?>"
                       class="<?php echo e(request()->routeIs('directory.*', 'profile.show') ? 'font-medium text-stone-800' : 'text-stone-600'); ?> underline-offset-2 hover:underline">
                        Directory
                    </a>
                    <a href="<?php echo e(route('profile.edit')); ?>"
                       class="<?php echo e(request()->routeIs('profile.edit', 'profile.classification.*') ? 'font-medium text-stone-800' : 'text-stone-600'); ?> underline-offset-2 hover:underline">
                        Your profile
                    </a>
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
    <footer class="mx-auto max-w-5xl px-4 pb-8 pt-4 text-xs text-stone-500">
        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-stone-200 pt-4">
            <span>Active Longevity Institute</span>
            <a href="<?php echo e(route('feedback.create', ['from' => url()->current()])); ?>" class="underline underline-offset-2 hover:text-stone-700">
                Tell the Institute what you think
            </a>
        </div>
    </footer>
</body>
</html>
<?php /**PATH C:\Users\Muhammad Ashhad\Downloads\04_Projects_Code\Longevity_Institute_Portal\active-longevity-portal\resources\views/components/app-layout.blade.php ENDPATH**/ ?>