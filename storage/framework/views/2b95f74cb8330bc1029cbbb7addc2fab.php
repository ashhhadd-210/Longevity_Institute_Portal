
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
    <div class="mx-auto max-w-2xl">
        <p class="text-sm">
            <a href="<?php echo e($case ? route('admin.cases.show', $case) : route('admin.queue.index')); ?>"
               class="text-stone-500 underline underline-offset-2">Back</a>
        </p>

        <h1 class="mt-3 text-xl font-semibold tracking-tight">
            <?php echo e($case ? 'Invite '.$case->display_name : 'Invite someone directly'); ?>

        </h1>

        <?php if (! ($case)): ?>
            <p class="mt-3 text-sm leading-relaxed text-stone-600">
                For someone you have found yourself, for example on LinkedIn. Their name is
                all that is needed. If you do not have an email address for them, choose to
                copy the link and send it however you normally speak to them.
            </p>
        <?php endif; ?>

        <?php if (isset($component)) { $__componentOriginal3d251db05a714ec6b2f220d3dccfef6d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3d251db05a714ec6b2f220d3dccfef6d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form-errors','data' => ['class' => 'mt-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-errors'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-6']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3d251db05a714ec6b2f220d3dccfef6d)): ?>
<?php $attributes = $__attributesOriginal3d251db05a714ec6b2f220d3dccfef6d; ?>
<?php unset($__attributesOriginal3d251db05a714ec6b2f220d3dccfef6d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3d251db05a714ec6b2f220d3dccfef6d)): ?>
<?php $component = $__componentOriginal3d251db05a714ec6b2f220d3dccfef6d; ?>
<?php unset($__componentOriginal3d251db05a714ec6b2f220d3dccfef6d); ?>
<?php endif; ?>

        
        <?php if($referral && ($referral->relationshipLabel() || $referral->acquaintance_note)): ?>
            <section class="mt-6 rounded-md bg-stone-50 p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-stone-500">
                    Context from <?php echo e($referral->referrer?->user?->fullName() ?? 'the referrer'); ?> — seen by the Institute only
                </p>
                <?php if($referral->relationshipLabel()): ?>
                    <p class="mt-2 text-sm"><span class="text-stone-500">Relationship:</span> <?php echo e($referral->relationshipLabel()); ?></p>
                <?php endif; ?>
                <?php if($referral->acquaintance_note): ?>
                    <p class="mt-1 whitespace-pre-line text-sm text-stone-700"><?php echo e($referral->acquaintance_note); ?></p>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <form method="POST"
              action="<?php echo e($case ? route('admin.invitations.store-for-case', $case) : route('admin.invitations.store')); ?>"
              class="mt-8 space-y-6">
            <?php echo csrf_field(); ?>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="first_name" class="block text-sm font-medium">First name</label>
                    <input id="first_name" name="first_name" type="text" required
                           maxlength="<?php echo e(\App\Support\FieldLimits::NAME); ?>"
                           value="<?php echo e(old('first_name', $suggestedName ? \Illuminate\Support\Str::before($suggestedName, ' ') : '')); ?>"
                           class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-medium">Family name</label>
                    <input id="last_name" name="last_name" type="text" required
                           maxlength="<?php echo e(\App\Support\FieldLimits::NAME); ?>"
                           value="<?php echo e(old('last_name', $suggestedName ? \Illuminate\Support\Str::afterLast($suggestedName, ' ') : '')); ?>"
                           class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
                </div>
            </div>

            <details class="text-sm">
                <summary class="cursor-pointer text-stone-500">Add a middle name or a preferred name</summary>
                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="middle_name" class="block text-sm font-medium">Middle name</label>
                        <input id="middle_name" name="middle_name" type="text"
                               maxlength="<?php echo e(\App\Support\FieldLimits::NAME); ?>"
                               value="<?php echo e(old('middle_name')); ?>"
                               class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
                    </div>
                    <div>
                        <label for="preferred_name" class="block text-sm font-medium">Preferred name</label>
                        <input id="preferred_name" name="preferred_name" type="text"
                               maxlength="<?php echo e(\App\Support\FieldLimits::NAME); ?>"
                               value="<?php echo e(old('preferred_name')); ?>"
                               class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
                        <p class="mt-1 text-xs text-stone-500">They can change this themselves when they register.</p>
                    </div>
                </div>
            </details>

            <fieldset class="rounded-md border border-stone-200 p-4">
                <legend class="px-1 text-sm font-medium">How should it reach them?</legend>

                <label class="flex items-start gap-3 text-sm">
                    <input type="radio" name="delivery_channel" value="email" class="mt-1"
                           <?php if(old('delivery_channel', 'email') === 'email'): echo 'checked'; endif; ?>>
                    <span>
                        <span class="font-medium">Send it from the Portal</span>
                        <span class="block text-xs text-stone-500">You will be able to see whether it was delivered.</span>
                    </span>
                </label>

                <div class="ml-7 mt-3">
                    <label for="email" class="block text-sm font-medium">Email address</label>
                    <input id="email" name="email" type="email"
                           value="<?php echo e(old('email', $suggestedEmail)); ?>"
                           class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
                </div>

                <label class="mt-5 flex items-start gap-3 text-sm">
                    <input type="radio" name="delivery_channel" value="copy_link" class="mt-1"
                           <?php if(old('delivery_channel') === 'copy_link'): echo 'checked'; endif; ?>>
                    <span>
                        <span class="font-medium">Give me the link to send myself</span>
                        <span class="block text-xs text-stone-500">
                            For LinkedIn or anywhere else. No email address is needed. The Portal
                            cannot see whether it arrived, so it will not pretend to.
                        </span>
                    </span>
                </label>
            </fieldset>

            <div>
                <label for="personal_message" class="block text-sm font-medium">
                    A sentence in your own words <span class="font-normal text-stone-400">(optional)</span>
                </label>
                <textarea id="personal_message" name="personal_message" rows="3" maxlength="2000"
                          class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm"><?php echo e(old('personal_message')); ?></textarea>
                <p class="mt-1 text-xs text-stone-500">This appears in the invitation exactly as you write it.</p>
            </div>

            <div class="border-t border-stone-200 pt-6">
                <button type="submit"
                        class="rounded-md bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
                    Generate invitation
                </button>
                <p class="mt-2 text-xs text-stone-500">
                    The link is shown on the next page, once. If you lose it, one click issues a
                    fresh one and retires the old.
                </p>
            </div>
        </form>
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
<?php /**PATH C:\Users\Muhammad Ashhad\Downloads\project longevity\active-longevity-portal\resources\views/admin/invitations/create.blade.php ENDPATH**/ ?>