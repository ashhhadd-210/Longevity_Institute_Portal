<?php if (isset($component)) { $__componentOriginal4619374cef299e94fd7263111d0abc69 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4619374cef299e94fd7263111d0abc69 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-layout','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="mb-6 flex items-baseline justify-between">
        <h1 class="text-lg font-semibold">Administration</h1>
        <p class="text-sm text-stone-500">Admission Release, Milestone 2</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <a href="<?php echo e(route('admin.queue.index')); ?>"
           class="rounded-md border border-stone-200 bg-white p-5 hover:border-stone-300">
            <p class="text-sm text-stone-500">Open applications</p>
            <p class="mt-1 text-2xl font-semibold"><?php echo e($openApplications); ?></p>
        </a>
        <a href="<?php echo e(route('admin.queue.index')); ?>"
           class="rounded-md border border-stone-200 bg-white p-5 hover:border-stone-300">
            <p class="text-sm text-stone-500">Open referrals</p>
            <p class="mt-1 text-2xl font-semibold"><?php echo e($openReferrals); ?></p>
        </a>
        <div class="rounded-md border border-stone-200 bg-white p-5">
            <p class="text-sm text-stone-500">Outstanding invitations</p>
            <p class="mt-1 text-2xl font-semibold"><?php echo e($outstandingInvitations); ?></p>
        </div>
        <div class="rounded-md border border-stone-200 bg-white p-5">
            <p class="text-sm text-stone-500">Collaborators</p>
            <p class="mt-1 text-2xl font-semibold"><?php echo e($collaboratorCount); ?></p>
        </div>
    </div>

    <div class="mt-8 flex flex-wrap gap-3">
        <a href="<?php echo e(route('admin.queue.index')); ?>"
           class="rounded-md bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
            Open the admission queue
        </a>
        <a href="<?php echo e(route('admin.invitations.create')); ?>"
           class="rounded-md border border-stone-300 bg-white px-4 py-2 text-sm font-medium hover:bg-stone-50">
            Invite someone directly
        </a>
    </div>

    <p class="mt-8 text-sm text-stone-500">
        Collaborator profiles, the directory and search arrive with Milestone 3 on
        this same foundation.
    </p>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4619374cef299e94fd7263111d0abc69)): ?>
<?php $attributes = $__attributesOriginal4619374cef299e94fd7263111d0abc69; ?>
<?php unset($__attributesOriginal4619374cef299e94fd7263111d0abc69); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4619374cef299e94fd7263111d0abc69)): ?>
<?php $component = $__componentOriginal4619374cef299e94fd7263111d0abc69; ?>
<?php unset($__componentOriginal4619374cef299e94fd7263111d0abc69); ?>
<?php endif; ?>
<?php /**PATH C:\Users\Muhammad Ashhad\Downloads\project longevity\active-longevity-portal\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>