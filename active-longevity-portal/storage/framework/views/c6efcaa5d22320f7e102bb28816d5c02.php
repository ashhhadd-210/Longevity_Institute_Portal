
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
    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6">

        <?php if(session('status')): ?>
            <div class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                <?php echo e(session('status')); ?>

            </div>
        <?php endif; ?>

        <h1 class="text-2xl font-semibold text-stone-800">Where your work belongs</h1>

        <details class="mt-4 rounded-lg border border-stone-200 bg-stone-50 p-4" <?php echo e($showOrientation ? 'open' : ''); ?>>
            <summary class="cursor-pointer text-sm font-medium text-stone-800">
                How the Institute thinks about Fields and Specialties
            </summary>
            <div class="mt-3 space-y-3 text-sm leading-relaxed text-stone-700">
                <p>You are not being asked to describe everything you know.</p>
                <p>
                    The purpose of this structure is simple: you should be able to recognize
                    where you belong, while also discovering the very different perspectives
                    represented throughout the Institute.
                </p>
                <p>
                    A collaborator's Field describes the discipline they bring to questions of
                    longer lives. It does not require longer lives to be the discipline itself.
                </p>
                <p>
                    A Specialty adds precision where it helps, and is entirely optional.
                </p>
                <p class="font-medium text-stone-800">Classify lightly. Describe richly.</p>
            </div>
        </details>

        <form method="POST" action="<?php echo e(route('profile.classification.update')); ?>" class="mt-8 space-y-10">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            
            <section>
                <h2 class="text-lg font-medium text-stone-800">Your Fields</h2>
                <p class="mt-1 text-sm text-stone-600">
                    Start typing the profession you would use to describe yourself.
                </p>

                <div data-selector="field" class="mt-4">
                    <input type="search" data-search
                           placeholder="Try actuary, geriatrician, housing, journalism"
                           autocomplete="off"
                           class="w-full rounded-md border-stone-300 text-sm shadow-sm focus:border-stone-500 focus:ring-stone-500">

                    <ul data-results class="mt-2 hidden divide-y divide-stone-100 rounded-md border border-stone-200 bg-white"></ul>

                    <ul data-chosen class="mt-4 space-y-2">
                        <?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex items-start justify-between gap-3 rounded-md border border-stone-200 bg-white px-3 py-2">
                                <div>
                                    <span class="text-sm font-medium text-stone-800"><?php echo e($field->name); ?></span>
                                    <span class="ml-2 rounded bg-stone-100 px-1.5 py-0.5 text-xs text-stone-600">Field</span>
                                    <?php if($field->descriptor): ?>
                                        <p class="mt-0.5 text-xs text-stone-500"><?php echo e($field->descriptor); ?></p>
                                    <?php endif; ?>
                                </div>
                                <button type="button" data-remove class="text-xs text-stone-500 underline">Remove</button>
                                <input type="hidden" name="field_ids[]" value="<?php echo e($field->id); ?>">
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            </section>

            
            <section>
                <h2 class="text-lg font-medium text-stone-800">
                    Your Specialties <span class="font-normal text-stone-400">(optional)</span>
                </h2>
                <p class="mt-1 text-sm text-stone-600">
                    Only if a narrower term genuinely describes your work. Many professions
                    do not need one.
                </p>

                <div data-selector="specialty" class="mt-4">
                    <input type="search" data-search
                           placeholder="Try geriatric medicine, longevity risk, health economics"
                           autocomplete="off"
                           class="w-full rounded-md border-stone-300 text-sm shadow-sm focus:border-stone-500 focus:ring-stone-500">

                    <ul data-results class="mt-2 hidden divide-y divide-stone-100 rounded-md border border-stone-200 bg-white"></ul>

                    <ul data-chosen class="mt-4 space-y-2">
                        <?php $__currentLoopData = $specialties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $specialty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex items-start justify-between gap-3 rounded-md border border-stone-200 bg-white px-3 py-2">
                                <div>
                                    <span class="text-sm font-medium text-stone-800"><?php echo e($specialty->name); ?></span>
                                    <span class="ml-2 rounded bg-stone-100 px-1.5 py-0.5 text-xs text-stone-600">Specialty</span>
                                    <?php if($specialty->disciplines->isNotEmpty()): ?>
                                        <p class="mt-0.5 text-xs text-stone-500">
                                            Found under <?php echo e($specialty->disciplines->pluck('name')->join(', ')); ?>

                                        </p>
                                    <?php endif; ?>
                                </div>
                                <button type="button" data-remove class="text-xs text-stone-500 underline">Remove</button>
                                <input type="hidden" name="specialty_ids[]" value="<?php echo e($specialty->id); ?>">
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            </section>

            <div class="border-t border-stone-200 pt-6">
                <button type="submit"
                        class="rounded-md bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
                    Save
                </button>
            </div>
        </form>

        
        <div class="mt-12 rounded-lg border border-stone-200 bg-stone-50 p-5">
            <h2 class="text-sm font-medium text-stone-800">Cannot find the right description?</h2>
            <p class="mt-1 text-sm text-stone-600">
                Tell us the term you use. Your profile is saved either way and nothing waits
                on us. We will look at it and decide whether it should join the Institute
                vocabulary.
            </p>

            <form method="POST" action="<?php echo e(route('profile.classification.propose')); ?>" class="mt-3 flex flex-col gap-2 sm:flex-row">
                <?php echo csrf_field(); ?>
                <select name="kind" class="rounded-md border-stone-300 text-sm shadow-sm sm:w-40">
                    <option value="field">As a Field</option>
                    <option value="specialty">As a Specialty</option>
                </select>
                <input type="text" name="term" required maxlength="300"
                       placeholder="The professional term you use"
                       class="flex-1 rounded-md border-stone-300 text-sm shadow-sm">
                <button type="submit"
                        class="rounded-md border border-stone-300 px-4 py-2 text-sm font-medium hover:bg-white">
                    Send it to the Institute
                </button>
            </form>

            <?php if($proposals->isNotEmpty()): ?>
                <ul class="mt-4 space-y-1 text-xs text-stone-600">
                    <?php $__currentLoopData = $proposals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proposal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li>
                            <span class="font-medium text-stone-800"><?php echo e($proposal->term); ?></span>
                            — <?php echo e($proposal->statusForCollaborator()); ?>

                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    
    <script>
        document.querySelectorAll('[data-selector]').forEach(function (panel) {
            var kind = panel.dataset.selector;
            var input = panel.querySelector('[data-search]');
            var results = panel.querySelector('[data-results]');
            var chosen = panel.querySelector('[data-chosen]');
            var timer = null;

            function alreadyChosen(id) {
                return !!chosen.querySelector('input[value="' + id + '"]');
            }

            function add(item) {
                if (alreadyChosen(item.id)) return;

                var li = document.createElement('li');
                li.className = 'flex items-start justify-between gap-3 rounded-md border border-stone-200 bg-white px-3 py-2';

                var left = document.createElement('div');
                var name = document.createElement('span');
                name.className = 'text-sm font-medium text-stone-800';
                name.textContent = item.name;
                var tag = document.createElement('span');
                tag.className = 'ml-2 rounded bg-stone-100 px-1.5 py-0.5 text-xs text-stone-600';
                tag.textContent = item.label;
                left.appendChild(name);
                left.appendChild(tag);

                var note = item.descriptor || (item.context ? 'Found under ' + item.context : '');
                if (note) {
                    var p = document.createElement('p');
                    p.className = 'mt-0.5 text-xs text-stone-500';
                    p.textContent = note;
                    left.appendChild(p);
                }

                var remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'text-xs text-stone-500 underline';
                remove.textContent = 'Remove';
                remove.addEventListener('click', function () { li.remove(); });

                var hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = (kind === 'field' ? 'field_ids[]' : 'specialty_ids[]');
                hidden.value = item.id;

                li.appendChild(left);
                li.appendChild(remove);
                li.appendChild(hidden);
                chosen.appendChild(li);

                input.value = '';
                results.classList.add('hidden');
                results.innerHTML = '';
            }

            function draw(items, notice) {
                results.innerHTML = '';
                if (notice) {
                    var info = document.createElement('li');
                    info.className = 'px-3 py-2 text-sm text-stone-600';
                    info.textContent = notice;
                    results.appendChild(info);
                    results.classList.remove('hidden');
                    return;
                }
                if (!items.length) { results.classList.add('hidden'); return; }

                items.forEach(function (item) {
                    var li = document.createElement('li');
                    li.className = 'cursor-pointer px-3 py-2 hover:bg-stone-50';
                    li.setAttribute('role', 'button');
                    li.tabIndex = 0;

                    var line = document.createElement('div');
                    line.className = 'text-sm text-stone-800';
                    line.textContent = item.name;

                    var tag = document.createElement('span');
                    tag.className = 'ml-2 rounded bg-stone-100 px-1.5 py-0.5 text-xs text-stone-600';
                    tag.textContent = item.label;
                    line.appendChild(tag);
                    li.appendChild(line);

                    var note = item.explanation || item.descriptor ||
                        (item.context ? 'Found under ' + item.context : '');
                    if (note) {
                        var small = document.createElement('p');
                        small.className = 'text-xs text-stone-500';
                        small.textContent = note;
                        li.appendChild(small);
                    }

                    li.addEventListener('click', function () { add(item); });
                    li.addEventListener('keydown', function (e) {
                        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); add(item); }
                    });
                    results.appendChild(li);
                });

                results.classList.remove('hidden');
            }

            input.addEventListener('input', function () {
                window.clearTimeout(timer);
                var q = input.value.trim();
                if (q.length < 2) { results.classList.add('hidden'); return; }

                timer = window.setTimeout(function () {
                    fetch('<?php echo e(route('expertise.search')); ?>?only=' + kind + '&q=' + encodeURIComponent(q), {
                        headers: { 'Accept': 'application/json' }
                    })
                        .then(function (r) { return r.json(); })
                        .then(function (data) { draw(data.results || [], data.notice || ''); })
                        .catch(function () { results.classList.add('hidden'); });
                }, 180);
            });
        });

        document.querySelectorAll('[data-chosen] [data-remove]').forEach(function (button) {
            button.addEventListener('click', function () { button.closest('li').remove(); });
        });
    </script>
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
<?php /**PATH C:\Users\Muhammad Ashhad\Downloads\04_Projects_Code\Longevity_Institute_Portal\active-longevity-portal\resources\views/collaborator/classification.blade.php ENDPATH**/ ?>