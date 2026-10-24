
<?php if (isset($component)) { $__componentOriginal58c831a7c3cbf004f2e66a23aed50e5b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal58c831a7c3cbf004f2e66a23aed50e5b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public-layout','data' => ['title' => 'This invitation link — Active Longevity Institute']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'This invitation link — Active Longevity Institute']); ?>
    <?php if($reason === 'already_registered'): ?>
        <h1 class="text-xl font-semibold tracking-tight">This invitation has already been used</h1>

        <p class="mt-4 text-sm leading-relaxed text-stone-600">
            An account has already been created with this invitation, which usually
            means you have registered before.
        </p>

        <p class="mt-3 text-sm leading-relaxed text-stone-600">
            You can <a href="<?php echo e(route('login')); ?>" class="underline underline-offset-2">sign in here</a>.
            If you did not expect this, please reply to the message that brought you
            here and the Institute will look into it.
        </p>
    <?php elseif($reason === 'withdrawn'): ?>
        <h1 class="text-xl font-semibold tracking-tight">This invitation is no longer open</h1>

        <p class="mt-4 text-sm leading-relaxed text-stone-600">
            The Institute has withdrawn this invitation. That is sometimes simply
            because a newer one was issued in its place.
        </p>

        <p class="mt-3 text-sm leading-relaxed text-stone-600">
            If you were expecting to join, please reply to the message that brought
            you here and someone will help.
        </p>
    <?php else: ?>
        <h1 class="text-xl font-semibold tracking-tight">This invitation link has lapsed</h1>

        <p class="mt-4 text-sm leading-relaxed text-stone-600">
            Invitation links are personal to one person and last for a limited time,
            so this one can no longer be used.
        </p>

        <p class="mt-3 text-sm leading-relaxed text-stone-600">
            Nothing has gone wrong and nothing is lost. Replying to the message that
            brought you here is enough, and a fresh link can be issued in a moment.
        </p>
    <?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal58c831a7c3cbf004f2e66a23aed50e5b)): ?>
<?php $attributes = $__attributesOriginal58c831a7c3cbf004f2e66a23aed50e5b; ?>
<?php unset($__attributesOriginal58c831a7c3cbf004f2e66a23aed50e5b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal58c831a7c3cbf004f2e66a23aed50e5b)): ?>
<?php $component = $__componentOriginal58c831a7c3cbf004f2e66a23aed50e5b; ?>
<?php unset($__componentOriginal58c831a7c3cbf004f2e66a23aed50e5b); ?>
<?php endif; ?>
<?php /**PATH C:\Users\Muhammad Ashhad\Downloads\04_Projects_Code\Longevity_Institute_Portal\active-longevity-portal\resources\views/public/invitation-unavailable.blade.php ENDPATH**/ ?>