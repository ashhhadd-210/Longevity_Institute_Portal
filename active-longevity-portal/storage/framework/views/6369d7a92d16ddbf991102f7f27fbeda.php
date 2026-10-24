
<?php if (isset($component)) { $__componentOriginal58c831a7c3cbf004f2e66a23aed50e5b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal58c831a7c3cbf004f2e66a23aed50e5b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public-layout','data' => ['title' => 'Complete your registration — Active Longevity Institute']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Complete your registration — Active Longevity Institute']); ?>
    <h1 class="text-xl font-semibold tracking-tight">
        <?php echo e(\App\Support\Salutation::greeting($invitation->salutationName())); ?>

    </h1>

    
    <p class="mt-4 text-sm leading-relaxed text-stone-600">
        You have been invited to join the collaborator community of the Active
        Longevity Institute<?php echo e($referrerName ? ', on the recommendation of '.$referrerName : ''); ?>.
    </p>

    <?php if(filled($invitation->personal_message)): ?>
        <blockquote class="mt-5 border-l-2 border-stone-300 pl-4 text-sm leading-relaxed text-stone-700">
            <?php echo e($invitation->personal_message); ?>

        </blockquote>
    <?php endif; ?>

    <p class="mt-5 text-sm leading-relaxed text-stone-600">
        Please confirm your details below. We have filled in what we were told, and
        anything that is not quite right is yours to correct.
    </p>

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

    <form method="POST" action="<?php echo e(route('invitations.accept', ['token' => $token])); ?>" class="mt-8 space-y-6">
        <?php echo csrf_field(); ?>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label for="first_name" class="block text-sm font-medium">First name</label>
                <input id="first_name" name="first_name" type="text" required
                       maxlength="<?php echo e(\App\Support\FieldLimits::NAME); ?>"
                       value="<?php echo e(old('first_name', $invitation->first_name)); ?>"
                       class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
            </div>
            <div>
                <label for="middle_name" class="block text-sm font-medium">
                    Middle name <span class="font-normal text-stone-400">(optional)</span>
                </label>
                <input id="middle_name" name="middle_name" type="text"
                       maxlength="<?php echo e(\App\Support\FieldLimits::NAME); ?>"
                       value="<?php echo e(old('middle_name', $invitation->middle_name)); ?>"
                       class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
            </div>
            <div>
                <label for="last_name" class="block text-sm font-medium">Family name</label>
                <input id="last_name" name="last_name" type="text" required
                       maxlength="<?php echo e(\App\Support\FieldLimits::NAME); ?>"
                       value="<?php echo e(old('last_name', $invitation->last_name)); ?>"
                       class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
            </div>
        </div>

        
        <div class="sm:w-1/3">
            <label for="name_suffix" class="block text-sm font-medium">
                Suffix <span class="font-normal text-stone-400">(optional)</span>
            </label>
            <input id="name_suffix" name="name_suffix" type="text"
                   maxlength="<?php echo e(\App\Support\FieldLimits::NAME_SUFFIX); ?>"
                   placeholder="Jr., Sr., III"
                   value="<?php echo e(old('name_suffix', $invitation->name_suffix)); ?>"
                   class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
        </div>

        
        <div>
            <label for="preferred_name" class="block text-sm font-medium">
                Name I go by
                <span class="font-normal text-stone-400">(optional)</span>
            </label>
            <input id="preferred_name" name="preferred_name" type="text"
                   maxlength="<?php echo e(\App\Support\FieldLimits::NAME); ?>"
                   value="<?php echo e(old('preferred_name', $invitation->preferred_name)); ?>"
                   class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
            <p class="mt-1 text-xs text-stone-500">
                How you would like to be addressed when someone speaks or writes to you, for
                example Thomas. Your full name remains your name of record.
            </p>
        </div>

        <?php if($invitation->email === null): ?>
            
            <div>
                <label for="email" class="block text-sm font-medium">Email address</label>
                <input id="email" name="email" type="email" required value="<?php echo e(old('email')); ?>"
                       class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
                <p class="mt-1 text-xs text-stone-500">This is the address you will sign in with.</p>
            </div>
        <?php else: ?>
            <div>
                <p class="block text-sm font-medium">Email address</p>
                <p class="mt-1 rounded-md bg-stone-50 px-3 py-2 text-sm text-stone-600"><?php echo e($invitation->email); ?></p>
                <p class="mt-1 text-xs text-stone-500">
                    This is the address your invitation was sent to, and the one you will sign in with.
                </p>
            </div>
        <?php endif; ?>

        <div>
            <label for="password" class="block text-sm font-medium">Choose a password</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                   class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium">Confirm your password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required
                   autocomplete="new-password"
                   class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
        </div>

        <div class="border-t border-stone-200 pt-6">
            <label class="flex items-start gap-3 text-sm text-stone-700">
                <input type="checkbox" name="privacy_consent" value="1" required
                       class="mt-0.5 rounded border-stone-300">
                <span>I have read the Institute's privacy notice and agree to it.</span>
            </label>

            <button type="submit"
                    class="mt-6 rounded-md bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
                Complete registration
            </button>
        </div>
    </form>
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
<?php /**PATH C:\Users\Muhammad Ashhad\Downloads\04_Projects_Code\Longevity_Institute_Portal\active-longevity-portal\resources\views/public/invitation-accept.blade.php ENDPATH**/ ?>