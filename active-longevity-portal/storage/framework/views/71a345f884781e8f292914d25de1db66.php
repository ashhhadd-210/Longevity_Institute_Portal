<?php if (isset($component)) { $__componentOriginalcb8170ac00b272413fe5b25f86fc5e3a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb8170ac00b272413fe5b25f86fc5e3a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.guest-layout','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('guest-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <h1 class="mb-6 text-lg font-semibold">Collaborator sign in</h1>

    <?php if(session('status')): ?>
        <p class="mb-4 rounded border border-stone-200 bg-stone-50 p-3 text-sm"><?php echo e(session('status')); ?></p>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <p class="mb-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-800">
            <?php echo e($errors->first()); ?>

        </p>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('login')); ?>" class="space-y-4">
        <?php echo csrf_field(); ?>
        <div>
            <label for="email" class="mb-1 block text-sm font-medium">Email address</label>
            <input id="email" name="email" type="email" value="<?php echo e(old('email')); ?>" required autofocus autocomplete="username"
                   class="w-full rounded border border-stone-300 px-3 py-2 text-sm focus:border-stone-500 focus:outline-none">
        </div>
        <div>
            <label for="password" class="mb-1 block text-sm font-medium">Password</label>
            <input id="password" name="password" type="password" required autocomplete="current-password"
                   class="w-full rounded border border-stone-300 px-3 py-2 text-sm focus:border-stone-500 focus:outline-none">
        </div>
        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-stone-600">
                <input type="checkbox" name="remember" class="rounded border-stone-300">
                Remember me
            </label>
            <a href="<?php echo e(route('password.request')); ?>" class="text-sm text-stone-600 underline-offset-2 hover:underline">
                Forgot your password?
            </a>
        </div>
        <button type="submit"
                class="w-full rounded bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
            Sign in
        </button>
    </form>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcb8170ac00b272413fe5b25f86fc5e3a)): ?>
<?php $attributes = $__attributesOriginalcb8170ac00b272413fe5b25f86fc5e3a; ?>
<?php unset($__attributesOriginalcb8170ac00b272413fe5b25f86fc5e3a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcb8170ac00b272413fe5b25f86fc5e3a)): ?>
<?php $component = $__componentOriginalcb8170ac00b272413fe5b25f86fc5e3a; ?>
<?php unset($__componentOriginalcb8170ac00b272413fe5b25f86fc5e3a); ?>
<?php endif; ?>
<?php /**PATH C:\Users\Muhammad Ashhad\Downloads\04_Projects_Code\Longevity_Institute_Portal\active-longevity-portal\resources\views/auth/login.blade.php ENDPATH**/ ?>