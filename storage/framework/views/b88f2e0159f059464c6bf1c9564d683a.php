
<?php if (isset($component)) { $__componentOriginal58c831a7c3cbf004f2e66a23aed50e5b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal58c831a7c3cbf004f2e66a23aed50e5b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.public-layout','data' => ['title' => 'Apply to join — Active Longevity Institute']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('public-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Apply to join — Active Longevity Institute']); ?>
    <h1 class="text-xl font-semibold tracking-tight">Apply to join the collaborator community</h1>

    <p class="mt-3 text-sm leading-relaxed text-stone-600">
        The Institute brings together professionals whose work touches the questions
        that longer lives raise. Tell us a little about yourself and why you would
        like to take part, and someone will read it properly.
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

    <form method="POST" action="<?php echo e(route('applications.store')); ?>" class="mt-8 space-y-6">
        <?php echo csrf_field(); ?>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label for="first_name" class="block text-sm font-medium">First name</label>
                <input id="first_name" name="first_name" type="text" required
                       maxlength="<?php echo e(\App\Support\FieldLimits::NAME); ?>"
                       value="<?php echo e(old('first_name')); ?>"
                       class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
            </div>
            <div>
                <label for="middle_name" class="block text-sm font-medium">
                    Middle name <span class="font-normal text-stone-400">(optional)</span>
                </label>
                <input id="middle_name" name="middle_name" type="text"
                       maxlength="<?php echo e(\App\Support\FieldLimits::NAME); ?>"
                       value="<?php echo e(old('middle_name')); ?>"
                       class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
            </div>
            <div>
                <label for="last_name" class="block text-sm font-medium">Family name</label>
                <input id="last_name" name="last_name" type="text" required
                       maxlength="<?php echo e(\App\Support\FieldLimits::NAME); ?>"
                       value="<?php echo e(old('last_name')); ?>"
                       class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
            </div>
        </div>

        <div>
            <label for="email" class="block text-sm font-medium">Email address</label>
            <input id="email" name="email" type="email" required value="<?php echo e(old('email')); ?>"
                   class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="organisation_text" class="block text-sm font-medium">
                    Organisation <span class="font-normal text-stone-400">(optional)</span>
                </label>
                <input id="organisation_text" name="organisation_text" type="text"
                       maxlength="<?php echo e(\App\Support\FieldLimits::ORGANISATION); ?>"
                       value="<?php echo e(old('organisation_text')); ?>"
                       class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
            </div>
            <div>
                <label for="country_code" class="block text-sm font-medium">
                    Country <span class="font-normal text-stone-400">(two-letter code, optional)</span>
                </label>
                <input id="country_code" name="country_code" type="text" maxlength="2"
                       value="<?php echo e(old('country_code')); ?>"
                       class="mt-1 w-full rounded-md border-stone-300 text-sm uppercase shadow-sm">
            </div>
        </div>

        <div>
            <label for="professional_title" class="block text-sm font-medium">
                Professional title <span class="font-normal text-stone-400">(optional)</span>
            </label>
            <input id="professional_title" name="professional_title" type="text"
                   maxlength="<?php echo e(\App\Support\FieldLimits::TITLE); ?>"
                   value="<?php echo e(old('professional_title')); ?>"
                   class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm">
            
            <p class="mt-1 text-xs text-stone-500">
                Please write your title in full. There is room for <?php echo e(\App\Support\FieldLimits::TITLE); ?> characters,
                so named chairs and joint appointments do not need to be shortened.
            </p>
        </div>

        <?php if($expertiseAreas->isNotEmpty()): ?>
            <fieldset>
                <legend class="text-sm font-medium">Areas of expertise <span class="font-normal text-stone-400">(optional)</span></legend>
                <div class="mt-2 grid gap-2 sm:grid-cols-2">
                    <?php $__currentLoopData = $expertiseAreas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $area): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex items-start gap-2 text-sm text-stone-700">
                            <input type="checkbox" name="expertise_claimed[]" value="<?php echo e($area->id); ?>"
                                   <?php if(in_array($area->id, old('expertise_claimed', []))): echo 'checked'; endif; ?>
                                   class="mt-0.5 rounded border-stone-300">
                            <span><?php echo e($area->name); ?></span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </fieldset>
        <?php endif; ?>

        <div>
            <label for="motivation" class="block text-sm font-medium">Why would you like to take part?</label>
            <textarea id="motivation" name="motivation" rows="6" required
                      class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm"><?php echo e(old('motivation')); ?></textarea>
        </div>

        <div class="border-t border-stone-200 pt-6">
            <button type="submit"
                    class="rounded-md bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
                Send application
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
<?php /**PATH C:\Users\Muhammad Ashhad\Downloads\project longevity\active-longevity-portal\resources\views/public/apply.blade.php ENDPATH**/ ?>