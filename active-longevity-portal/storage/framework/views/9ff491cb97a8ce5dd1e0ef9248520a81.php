<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e(isset($title) ? $title.' · ' : ''); ?>Collaborator Portal · Active Longevity Institute</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="min-h-screen bg-stone-100 text-stone-800 antialiased">
    <div class="mx-auto flex min-h-screen max-w-2xl flex-col px-4 py-10">
        <header class="mb-8">
            <?php if (isset($component)) { $__componentOriginal2d0dd047bc9f587ee4fcbc3aad9dee65 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2d0dd047bc9f587ee4fcbc3aad9dee65 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.portal-mark','data' => ['size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('portal-mark'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'lg']); ?>
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
        </header>

        <main class="rounded-md border border-stone-200 bg-white p-8 shadow-sm">
            <?php echo e($slot); ?>

        </main>

        <footer class="mt-8 text-xs text-stone-400">
            <p>Active Longevity Institute · Admission to the collaborator community is by decision of the Institute.</p>
        </footer>
    </div>
</body>
</html>
<?php /**PATH C:\Users\Muhammad Ashhad\Downloads\04_Projects_Code\Longevity_Institute_Portal\active-longevity-portal\resources\views/components/public-layout.blade.php ENDPATH**/ ?>