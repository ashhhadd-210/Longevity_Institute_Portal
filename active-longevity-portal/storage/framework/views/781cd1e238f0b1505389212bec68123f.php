
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
    <div class="flex items-baseline justify-between">
        <div>
            <h1 class="text-xl font-semibold tracking-tight">Admission queue</h1>
            <p class="mt-1 text-sm text-stone-500">
                <?php echo e($openCount); ?> <?php echo e(\Illuminate\Support\Str::plural('case', $openCount)); ?> open
            </p>
        </div>

        <a href="<?php echo e(route('admin.invitations.create')); ?>"
           class="rounded-md bg-stone-800 px-3 py-2 text-sm font-medium text-white hover:bg-stone-700">
            Invite someone directly
        </a>
    </div>

    <?php if(session('status')): ?>
        <div class="mt-6 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            <?php echo e(session('status')); ?>

        </div>
    <?php endif; ?>

    <nav class="mt-6 flex gap-4 border-b border-stone-200 text-sm">
        <?php $__currentLoopData = ['open' => 'Open', 'decided' => 'Decided', 'all' => 'Everything']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('admin.queue.index', ['show' => $key])); ?>"
               class="-mb-px border-b-2 px-1 pb-2 <?php echo e($show === $key ? 'border-stone-800 font-medium text-stone-800' : 'border-transparent text-stone-500 hover:text-stone-700'); ?>">
                <?php echo e($label); ?>

            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </nav>

    <?php if($items->isEmpty()): ?>
        <p class="mt-8 text-sm text-stone-500">Nothing here at the moment.</p>
    <?php else: ?>
        <div class="mt-6 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-stone-200 text-xs uppercase tracking-wide text-stone-500">
                    <tr>
                        <th class="py-2 pr-4 font-medium">Name</th>
                        <th class="py-2 pr-4 font-medium">How they arrived</th>
                        <th class="py-2 pr-4 font-medium">Status</th>
                        <th class="py-2 pr-4 font-medium">Waiting since</th>
                        <th class="py-2 font-medium"><span class="sr-only">Open</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php ($subject = $row['subject']); ?>
                        <tr class="align-top">
                            <td class="py-3 pr-4 font-medium text-stone-800"><?php echo e($row['name']); ?></td>
                            <td class="py-3 pr-4 text-stone-600">
                                <?php if($row['kind'] === 'referral'): ?>
                                    Referred by <?php echo e($subject->referrer?->user?->fullName() ?? 'a collaborator'); ?>

                                <?php else: ?>
                                    Applied through the website
                                <?php endif; ?>
                            </td>
                            <td class="py-3 pr-4">
                                <span class="rounded-full bg-stone-100 px-2 py-0.5 text-xs text-stone-700">
                                    <?php echo e($subject->status->label()); ?>

                                </span>
                            </td>
                            <td class="py-3 pr-4 text-stone-500"><?php echo e($row['arrived']->diffForHumans()); ?></td>
                            <td class="py-3 text-right">
                                <?php if($subject->candidate_case_id): ?>
                                    <a href="<?php echo e(route('admin.cases.show', $subject->candidate_case_id)); ?>"
                                       class="underline underline-offset-2">Open</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
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
<?php /**PATH C:\Users\Muhammad Ashhad\Downloads\04_Projects_Code\Longevity_Institute_Portal\active-longevity-portal\resources\views/admin/queue/index.blade.php ENDPATH**/ ?>