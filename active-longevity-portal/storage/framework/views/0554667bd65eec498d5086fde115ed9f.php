
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
    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6">

        <h1 class="text-2xl font-semibold text-stone-800">The collaborator community</h1>
        <p class="mt-1 text-sm text-stone-600">
            Search by name, by Professional Identity, or by Field and Specialty.
        </p>

        <form method="GET" action="<?php echo e(route('directory.index')); ?>" class="mt-6 space-y-3">
            <input type="search" name="q" value="<?php echo e($filters['q'] ?? ''); ?>"
                   placeholder="A name, a title, or an area of work"
                   class="w-full rounded-md border-stone-300 text-sm shadow-sm focus:border-stone-500 focus:ring-stone-500">

            <div class="grid gap-3 sm:grid-cols-3">
                <select name="field" class="rounded-md border-stone-300 text-sm shadow-sm">
                    <option value="">Any Field</option>
                    <?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($field->id); ?>" <?php if(($filters['field'] ?? null) == $field->id): echo 'selected'; endif; ?>>
                            <?php echo e($field->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>

                <select name="country" class="rounded-md border-stone-300 text-sm shadow-sm">
                    <option value="">Any country</option>
                    <?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($country); ?>" <?php if(($filters['country'] ?? null) === $country): echo 'selected'; endif; ?>>
                            <?php echo e(\App\Support\Countries::name($country)); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>

                <div class="flex gap-2">
                    <button type="submit"
                            class="rounded-md bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
                        Search
                    </button>
                    <?php if(array_filter($filters)): ?>
                        <a href="<?php echo e(route('directory.index')); ?>"
                           class="rounded-md border border-stone-300 px-4 py-2 text-sm hover:bg-white">Clear</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <p class="mt-6 text-sm text-stone-500">
            <?php echo e($profiles->total()); ?> <?php echo e(\Illuminate\Support\Str::plural('collaborator', $profiles->total())); ?>

        </p>

        <?php if($profiles->isEmpty()): ?>
            <div class="mt-4 rounded-lg border border-stone-200 bg-stone-50 p-6 text-sm text-stone-600">
                <?php if(array_filter($filters)): ?>
                    <p>Nothing matched that search.</p>
                    <p class="mt-1">
                        The community is still small, so a term that finds nobody today may well
                        find somebody next month.
                    </p>
                <?php else: ?>
                    <p>The directory is not yet populated.</p>
                    <p class="mt-1">
                        Collaborators appear here once their profile carries enough for somebody
                        else to recognize them.
                    </p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <ul class="mt-4 divide-y divide-stone-100 rounded-lg border border-stone-200 bg-white">
                <?php $__currentLoopData = $profiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $person = $entry->collaborator?->user; ?>
                    <?php $credentials = $entry->approvedCredentials; ?>
                    <li class="p-4 sm:p-5">
                        <a href="<?php echo e(route('profile.show', $entry)); ?>" class="flex items-start gap-4">
                            <?php if(filled($entry->photo_path)): ?>
                                <img src="<?php echo e(\Illuminate\Support\Facades\Storage::url($entry->photo_path)); ?>"
                                     alt="" class="h-20 w-15 flex-none rounded border border-stone-200 object-cover" style="width:3.75rem">
                            <?php endif; ?>
                            <div class="min-w-0">
                                <p class="font-medium text-stone-800">
                                    <?php echo e($person?->fullName()); ?><?php if($credentials->isNotEmpty()): ?><span class="font-normal text-stone-500">, <?php echo e($credentials->pluck('acronym')->join(', ')); ?></span><?php endif; ?>
                                </p>

                                <?php if(filled($entry->professional_title)): ?>
                                    <p class="mt-0.5 text-sm text-stone-600"><?php echo e($entry->professional_title); ?></p>
                                <?php endif; ?>

                                <?php if(filled($entry->country_code)): ?>
                                    <p class="mt-0.5 text-xs text-stone-500"><?php echo e(\App\Support\Countries::name($entry->country_code)); ?></p>
                                <?php endif; ?>
                            </div>
                        </a>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>

            <div class="mt-6"><?php echo e($profiles->links()); ?></div>
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
<?php /**PATH C:\Users\Muhammad Ashhad\Downloads\04_Projects_Code\Longevity_Institute_Portal\active-longevity-portal\resources\views/collaborator/directory.blade.php ENDPATH**/ ?>