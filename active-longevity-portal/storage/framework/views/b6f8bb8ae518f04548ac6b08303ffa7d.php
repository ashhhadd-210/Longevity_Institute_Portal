
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
    <?php
        $person = $profile->collaborator?->user;
        $credentials = $profile->approvedCredentials;
    ?>

    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6">

        <a href="<?php echo e(route('directory.index')); ?>" class="text-sm underline underline-offset-2">Back to the directory</a>

        <header class="mt-6 flex flex-col gap-5 sm:flex-row sm:items-start">
            <?php if(filled($profile->photo_path)): ?>
                <img src="<?php echo e(\Illuminate\Support\Facades\Storage::url($profile->photo_path)); ?>"
                     alt="<?php echo e($person?->fullName()); ?>"
                     class="h-32 w-24 flex-none rounded-md border border-stone-200 object-cover">
            <?php endif; ?>

            <div class="min-w-0">
                <h1 class="text-2xl font-semibold text-stone-800">
                    <?php echo e($person?->fullName()); ?><?php if($credentials->isNotEmpty()): ?><span class="text-stone-500">, <?php echo e($credentials->pluck('acronym')->join(', ')); ?></span><?php endif; ?>
                </h1>

                <?php if(filled($person?->preferred_name) && $person->preferred_name !== $person->first_name): ?>
                    <p class="mt-0.5 text-sm text-stone-500">Goes by <?php echo e($person->preferred_name); ?></p>
                <?php endif; ?>

                <?php if(filled($profile->professional_title)): ?>
                    <p class="mt-2 text-stone-700"><?php echo e($profile->professional_title); ?></p>
                <?php endif; ?>

                <?php if($profile->organisation): ?>
                    <p class="mt-1 text-sm text-stone-600"><?php echo e($profile->organisation->name); ?></p>
                <?php endif; ?>

                <?php if(filled($profile->country_code)): ?>
                    <p class="mt-1 text-sm text-stone-500"><?php echo e(\App\Support\Countries::name($profile->country_code)); ?></p>
                <?php endif; ?>
            </div>
        </header>

        <?php if($credentials->isNotEmpty()): ?>
            <p class="mt-3 text-xs text-stone-500">
                <?php $__currentLoopData = $credentials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $credential): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span class="mr-3"><?php echo e($credential->acronym); ?> — <?php echo e($credential->describe()); ?></span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </p>
        <?php endif; ?>

        <?php if(filled($profile->one_line_summary)): ?>
            <p class="mt-6 text-lg leading-relaxed text-stone-700"><?php echo e($profile->one_line_summary); ?></p>
        <?php endif; ?>

        <?php if($profile->currentPositions->isNotEmpty()): ?>
            <section class="mt-8">
                <h2 class="text-xs font-medium uppercase tracking-wide text-stone-500">Current positions</h2>
                <ul class="mt-2 space-y-2">
                    <?php $__currentLoopData = $profile->currentPositions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $pos = $entry->payload; ?>
                        <li>
                            <?php if(filled($pos['title'] ?? null)): ?>
                                <p class="text-stone-800"><?php echo e($pos['title']); ?></p>
                            <?php endif; ?>
                            <?php
                                $where = array_filter([
                                    $pos['organisation'] ?? null,
                                    $pos['department'] ?? null,
                                    \App\Support\Countries::name($pos['country'] ?? null),
                                ]);
                            ?>
                            <?php if($where !== [] || filled($pos['start_year'] ?? null)): ?>
                                <p class="text-sm text-stone-600">
                                    <?php echo e(implode(', ', $where)); ?><?php if(filled($pos['start_year'] ?? null)): ?><?php if($where !== []): ?> · <?php endif; ?> since <?php echo e($pos['start_year']); ?><?php endif; ?>
                                </p>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </section>
        <?php endif; ?>

        <?php if($profile->fields->isNotEmpty() || $profile->specialties->isNotEmpty()): ?>
            <section class="mt-8">
                <h2 class="text-xs font-medium uppercase tracking-wide text-stone-500">Fields and Specialties</h2>
                <div class="mt-2 flex flex-wrap gap-2">
                    <?php $__currentLoopData = $profile->fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('directory.index', ['field' => $field->id])); ?>"
                           class="rounded-full bg-stone-100 px-3 py-1 text-sm text-stone-800 hover:bg-stone-200">
                            <?php echo e($field->name); ?>

                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php $__currentLoopData = $profile->specialties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $specialty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="rounded-full border border-stone-200 px-3 py-1 text-sm text-stone-600"
                              title="<?php echo e($specialty->disciplines->pluck('name')->join(', ')); ?>">
                            <?php echo e($specialty->name); ?>

                        </span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if(filled($profile->about)): ?>
            <section class="mt-8">
                <h2 class="text-xs font-medium uppercase tracking-wide text-stone-500">Additional Information</h2>
                <div class="mt-2 whitespace-pre-line leading-relaxed text-stone-700"><?php echo e($profile->about); ?></div>
            </section>
        <?php endif; ?>

        <?php if($profile->links->isNotEmpty()): ?>
            <section class="mt-8">
                <h2 class="text-xs font-medium uppercase tracking-wide text-stone-500">Links</h2>
                <ul class="mt-2 space-y-1">
                    <?php $__currentLoopData = $profile->links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li>
                            <a href="<?php echo e($link->url); ?>" target="_blank" rel="noopener noreferrer"
                               class="text-sm underline underline-offset-2"><?php echo e($link->label); ?></a>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </section>
        <?php endif; ?>

        <?php if(filled($profile->collaboration_availability)): ?>
            <section class="mt-8 rounded-lg border border-stone-200 bg-stone-50 p-4">
                <h2 class="text-xs font-medium uppercase tracking-wide text-stone-500">Open to</h2>
                <p class="mt-1 text-sm text-stone-700"><?php echo e($profile->collaboration_availability); ?></p>
            </section>
        <?php endif; ?>

        
        <?php
            $viewerProfile = auth()->user()?->collaborator?->profile;
            $isSelf = $viewerProfile !== null && $viewerProfile->id === $profile->id;
            $alreadySent = $viewerProfile !== null && \App\Models\CollaborationInterest::query()
                ->where('from_profile_id', $viewerProfile->id)
                ->where('to_profile_id', $profile->id)
                ->exists();
        ?>

        <?php if(! $isSelf && $viewerProfile !== null): ?>
            <section class="mt-8 rounded-lg border border-stone-200 bg-white p-5">
                <?php if(session('status')): ?>
                    <p class="mb-3 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-900"><?php echo e(session('status')); ?></p>
                <?php endif; ?>

                <?php if($alreadySent): ?>
                    <p class="text-sm text-stone-600">
                        You have registered an interest in working with <?php echo e($person?->preferred_name ?: $person?->first_name); ?>.
                        The Institute has it and will pass it on.
                    </p>
                <?php elseif($profile->accepts_collaboration_interest): ?>
                    <h2 class="text-sm font-medium text-stone-800">Interested in working together?</h2>
                    <p class="mt-1 text-sm text-stone-600">
                        Let the Institute know, and it will pass your interest on.
                    </p>
                    <form method="POST" action="<?php echo e(route('profile.interest', $profile)); ?>" class="mt-3 space-y-2">
                        <?php echo csrf_field(); ?>
                        <textarea name="note" rows="2" maxlength="1000"
                                  placeholder="Optional: what you would like to explore together"
                                  class="w-full rounded-md border-stone-300 text-sm shadow-sm"><?php echo e(old('note')); ?></textarea>
                        <button type="submit"
                                class="rounded-md bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
                            Register an interest
                        </button>
                    </form>
                <?php else: ?>
                    <p class="text-sm text-stone-500">
                        <?php echo e($person?->preferred_name ?: $person?->first_name); ?> is not currently taking approaches through the Portal.
                    </p>
                <?php endif; ?>
            </section>
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
<?php /**PATH C:\Users\Muhammad Ashhad\Downloads\04_Projects_Code\Longevity_Institute_Portal\active-longevity-portal\resources\views/collaborator/profile-show.blade.php ENDPATH**/ ?>