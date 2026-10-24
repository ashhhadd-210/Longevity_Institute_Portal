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
    <div class="mx-auto max-w-xl">
        <h1 class="mb-2 text-lg font-semibold">Two step verification</h1>

        <?php if($required && ! $confirmed): ?>
            <p class="mb-6 text-sm text-stone-600">
                Administrative accounts are protected by a six digit code from your phone, in
                addition to your password. This takes about a minute, once.
            </p>
        <?php endif; ?>

        <?php if(session('status') === 'two-factor-authentication-enabled'): ?>
            <p class="mb-4 rounded border border-stone-200 bg-stone-50 p-3 text-sm">
                Scan the code below, then confirm with a code from your authenticator application.
            </p>
        <?php elseif(session('status') === 'two-factor-authentication-confirmed'): ?>
            <p class="mb-4 rounded border border-stone-200 bg-stone-50 p-3 text-sm">
                Two-factor authentication is confirmed. Store your recovery codes somewhere safe.
            </p>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <p class="mb-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                <?php echo e($errors->first()); ?>

            </p>
        <?php endif; ?>

        <?php if(! $enabled): ?>
            <form method="POST" action="<?php echo e(url('user/two-factor-authentication')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit"
                        class="rounded bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
                    Begin enrollment
                </button>
            </form>
        <?php elseif(! $confirmed): ?>
            <div class="space-y-6">
                <ol class="list-decimal space-y-2 pl-5 text-sm text-stone-700">
                    <li>On your phone, open an authenticator app such as Google Authenticator or
                        Microsoft Authenticator. Both are free in the App Store and Google Play.</li>
                    <li>In the app, choose to add an account, then scan the QR code below with your
                        phone's camera.</li>
                    <li>The app now shows a six digit code for the Collaborator Portal. Type it in the
                        box below and press Confirm.</li>
                </ol>
                <div class="rounded border border-stone-200 bg-white p-4">
                    <div class="inline-block bg-white p-2" data-qr><?php echo auth()->user()->twoFactorQrCodeSvg(); ?></div>
                    <p class="mt-3 text-xs text-stone-500">
                        If you cannot scan it, choose "enter a setup key" in the app and type:
                        <span class="font-mono"><?php echo e(decrypt(auth()->user()->two_factor_secret)); ?></span>
                    </p>
                </div>
                <form method="POST" action="<?php echo e(url('user/confirmed-two-factor-authentication')); ?>" class="space-y-4">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label for="code" class="mb-1 block text-sm font-medium">Six digit code from the app</label>
                        <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" required
                               class="w-full rounded border border-stone-300 px-3 py-2 text-sm focus:border-stone-500 focus:outline-none">
                    </div>
                    <button type="submit"
                            class="rounded bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
                        Confirm
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <p class="text-sm text-stone-600">Two step verification is set up. From now on, after your
                    password, the Portal will ask for the six digit code shown in your authenticator app.</p>
                <div class="rounded border border-stone-200 bg-white p-4">
                    <p class="mb-2 text-sm font-medium">Recovery codes</p>
                    <ul class="space-y-1 font-mono text-sm text-stone-700">
                        <?php $__currentLoopData = auth()->user()->recoveryCodes(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($code); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                    <p class="mt-3 text-xs text-stone-500">
                        Please keep these somewhere safe. If you ever lose your phone, any one of these
                        codes lets you sign in once in place of the six digit code.
                    </p>
                </div>
                <?php if(auth()->user()->hasPermission('platform.admin.access')): ?>
                    <a href="<?php echo e(route('admin.dashboard')); ?>"
                       class="inline-block rounded bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
                        Continue
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
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
<?php /**PATH C:\Users\Muhammad Ashhad\Downloads\04_Projects_Code\Longevity_Institute_Portal\active-longevity-portal\resources\views/auth/two-factor-setup.blade.php ENDPATH**/ ?>