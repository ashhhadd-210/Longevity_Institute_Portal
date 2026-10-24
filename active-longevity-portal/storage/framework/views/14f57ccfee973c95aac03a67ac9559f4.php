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
        <?php if(session('just_admitted')): ?>
            <div class="mb-6 rounded-md border border-green-200 bg-green-50 p-5">
                <p class="font-medium text-green-900">Welcome to the Institute</p>
                <p class="mt-1 text-sm text-green-800">
                    Your registration is complete and you are now a collaborator.
                </p>
            </div>
        <?php endif; ?>

        <div class="rounded-md border border-stone-200 bg-white p-8">
            
            <h1 class="mb-2 text-lg font-semibold">
                <?php echo e(\App\Support\Salutation::greeting(auth()->user(), 'Welcome')); ?>

            </h1>

            <p class="text-sm text-stone-600">
                You are signed in to the Institute's Collaborator Portal.
            </p>

            <?php if(auth()->user()->collaborator): ?>
                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    <a href="<?php echo e(route('profile.edit')); ?>"
                       class="flex flex-col rounded-md border border-stone-300 p-4 hover:border-stone-500 hover:bg-stone-50">
                        <p class="text-sm font-medium text-stone-800">Your profile</p>
                        <p class="mt-1 flex-1 text-xs text-stone-600">
                            Build and update your profile. It saves as you go, and appears in
                            the Collaborator Directory once it is ready.
                        </p>
                        <span class="mt-3 inline-block self-start rounded-md bg-stone-800 px-3 py-1.5 text-xs font-medium text-white">Open your profile</span>
                    </a>
                    <a href="<?php echo e(route('directory.index')); ?>"
                       class="flex flex-col rounded-md border border-stone-300 p-4 hover:border-stone-500 hover:bg-stone-50">
                        <p class="text-sm font-medium text-stone-800">Collaborator Directory</p>
                        <p class="mt-1 flex-1 text-xs text-stone-600">
                            Find other collaborators by name, by Field and Specialty, or by country.
                        </p>
                        <span class="mt-3 inline-block self-start rounded-md bg-stone-800 px-3 py-1.5 text-xs font-medium text-white">Open the Directory</span>
                    </a>
                </div>

                <div class="mt-6 rounded-md bg-stone-50 p-4 text-xs text-stone-600">
                    <p class="font-medium text-stone-700">The menu at the top of every page</p>
                    <ul class="mt-1 space-y-0.5">
                        <li><strong>Directory</strong>: find and view other collaborators.</li>
                        <li><strong>Your profile</strong>: build or update your own profile.</li>
                        <li><strong>Recommend</strong>: suggest a professional the Institute might invite.</li>
                    </ul>
                </div>

                <div class="mt-6 border-t border-stone-100 pt-6">
                    <p class="text-sm font-medium">Know someone who belongs here?</p>
                    <p class="mt-1 text-sm text-stone-600">
                        The Institute grows by recommendation. Nothing is sent to the person
                        you name unless the Institute decides to invite them.
                    </p>
                    <a href="<?php echo e(route('referrals.create')); ?>"
                       class="mt-4 inline-block rounded-md bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
                        Recommend a professional
                    </a>
                </div>
            <?php endif; ?>
        </div>
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
<?php /**PATH C:\Users\Muhammad Ashhad\Downloads\04_Projects_Code\Longevity_Institute_Portal\active-longevity-portal\resources\views/home.blade.php ENDPATH**/ ?>