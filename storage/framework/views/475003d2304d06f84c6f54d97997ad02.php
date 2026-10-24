
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
    <div class="mx-auto max-w-4xl">
        <p class="text-sm">
            <a href="<?php echo e(route('admin.queue.index')); ?>" class="text-stone-500 underline underline-offset-2">Back to the queue</a>
        </p>

        <h1 class="mt-3 text-xl font-semibold tracking-tight"><?php echo e($case->display_name); ?></h1>
        <?php if($case->email): ?>
            <p class="mt-1 text-sm text-stone-500"><?php echo e($case->email); ?></p>
        <?php endif; ?>

        <?php if(session('status')): ?>
            <div class="mt-6 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                <?php echo e(session('status')); ?>

            </div>
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

        
        <?php if($duplicates->isNotEmpty()): ?>
            <section class="mt-6 rounded-md border border-amber-200 bg-amber-50 p-4">
                <h2 class="text-sm font-medium text-amber-900">This may be the same person as another record</h2>
                <div class="mt-3 space-y-3">
                    <?php $__currentLoopData = $duplicates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $match): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-md bg-white px-3 py-2">
                            <div class="text-sm">
                                <a href="<?php echo e(route('admin.cases.show', $match->case)); ?>"
                                   class="font-medium underline underline-offset-2"><?php echo e($match->case->display_name); ?></a>
                                <span class="text-stone-500">, <?php echo e($match->confidence->label()); ?></span>
                                <p class="mt-0.5 text-xs text-stone-500"><?php echo e($match->confidence->explanation()); ?></p>
                            </div>
                            <?php if($match->isJoinable()): ?>
                                <form method="POST" action="<?php echo e(route('admin.cases.join', $case)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="absorbed_case_id" value="<?php echo e($match->case->id); ?>">
                                    <button type="submit" class="rounded-md border border-stone-300 px-3 py-1.5 text-xs font-medium hover:bg-stone-50">
                                        These are the same person
                                    </button>
                                </form>
                            <?php else: ?>
                                
                                <p class="max-w-xs text-xs text-stone-600">
                                    <?php echo e($match->settled); ?>

                                    If they are the same person, work from that case rather than this one.
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
        <?php endif; ?>

        
        <section class="mt-8">
            <h2 class="text-sm font-medium uppercase tracking-wide text-stone-500">Submissions</h2>

            <?php $__currentLoopData = $case->applications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $application): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article class="mt-3 rounded-md border border-stone-200 bg-white p-5">
                    <div class="flex items-baseline justify-between">
                        <h3 class="font-medium">Application through the website</h3>
                        <span class="text-xs text-stone-500"><?php echo e($application->submitted_at->format('j F Y')); ?></span>
                    </div>
                    <dl class="mt-3 grid gap-x-6 gap-y-2 text-sm sm:grid-cols-2">
                        <div><dt class="text-stone-500">Name</dt><dd><?php echo e($application->fullName()); ?></dd></div>
                        <div><dt class="text-stone-500">Email</dt><dd><?php echo e($application->email); ?></dd></div>
                        <?php if($application->professional_title): ?>
                            
                            <div class="sm:col-span-2"><dt class="text-stone-500">Title</dt><dd class="break-words"><?php echo e($application->professional_title); ?></dd></div>
                        <?php endif; ?>
                        <?php if($application->organisation_text): ?>
                            <div><dt class="text-stone-500">Organisation</dt><dd class="break-words"><?php echo e($application->organisation_text); ?></dd></div>
                        <?php endif; ?>
                        <?php if($application->country_code): ?>
                            <div><dt class="text-stone-500">Country</dt><dd><?php echo e($application->country_code); ?></dd></div>
                        <?php endif; ?>
                    </dl>
                    <?php if($application->motivation): ?>
                        <p class="mt-4 whitespace-pre-line border-t border-stone-100 pt-4 text-sm leading-relaxed text-stone-700"><?php echo e($application->motivation); ?></p>
                    <?php endif; ?>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php $__currentLoopData = $case->referrals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $referral): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article class="mt-3 rounded-md border border-stone-200 bg-white p-5">
                    <div class="flex items-baseline justify-between">
                        <h3 class="font-medium">Referred by <?php echo e($referral->referrer?->user?->fullName() ?? 'a collaborator'); ?></h3>
                        <span class="text-xs text-stone-500"><?php echo e($referral->created_at->format('j F Y')); ?></span>
                    </div>
                    <dl class="mt-3 grid gap-x-6 gap-y-2 text-sm sm:grid-cols-2">
                        <div><dt class="text-stone-500">Name</dt><dd><?php echo e($referral->candidateFullName()); ?></dd></div>
                        <div><dt class="text-stone-500">Email</dt><dd><?php echo e($referral->candidate_email); ?></dd></div>
                        <?php if($referral->candidate_title): ?>
                            <div class="sm:col-span-2"><dt class="text-stone-500">Title</dt><dd class="break-words"><?php echo e($referral->candidate_title); ?></dd></div>
                        <?php endif; ?>
                        <?php if($referral->candidate_organisation): ?>
                            <div><dt class="text-stone-500">Organisation</dt><dd class="break-words"><?php echo e($referral->candidate_organisation); ?></dd></div>
                        <?php endif; ?>
                    </dl>

                    <?php if($referral->reason): ?>
                        <p class="mt-4 whitespace-pre-line border-t border-stone-100 pt-4 text-sm leading-relaxed text-stone-700"><?php echo e($referral->reason); ?></p>
                    <?php endif; ?>

                    
                    <?php if($referral->relationshipLabel() || $referral->acquaintance_note): ?>
                        <div class="mt-4 rounded-md bg-stone-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-stone-500">
                                Seen by the Institute only
                            </p>
                            <?php if($referral->relationshipLabel()): ?>
                                <p class="mt-2 text-sm"><span class="text-stone-500">Relationship:</span> <?php echo e($referral->relationshipLabel()); ?></p>
                            <?php endif; ?>
                            <?php if($referral->acquaintance_note): ?>
                                <p class="mt-1 whitespace-pre-line text-sm text-stone-700"><?php echo e($referral->acquaintance_note); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </section>

        
        <?php $__currentLoopData = $openSubjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php ($type = $subject instanceof \App\Models\Application ? 'application' : 'referral'); ?>
            <?php ($recipient = $type === 'referral' ? 'the referrer' : 'the applicant'); ?>

            <section class="mt-8 rounded-md border border-stone-200 bg-white p-5">
                <h2 class="text-sm font-medium">
                    Decide on the <?php echo e($type); ?>

                    <span class="ml-2 rounded-full bg-stone-100 px-2 py-0.5 text-xs font-normal text-stone-600">
                        <?php echo e($subject->status->label()); ?>

                    </span>
                </h2>

                <?php if($subject->status === \App\Enums\ApplicationStatus::SetAside || $subject->status === \App\Enums\ReferralStatus::SetAside): ?>
                    <div class="mt-4 rounded-md bg-stone-50 p-4 text-sm">
                        <p class="text-stone-700">
                            Set aside<?php echo e($subject->set_aside_at ? ' on '.$subject->set_aside_at->format('j F Y') : ''); ?>.
                            Nothing was sent to anybody.
                        </p>
                        <?php if($subject->set_aside_reason): ?>
                            <p class="mt-1 text-stone-600"><?php echo e($subject->set_aside_reason); ?></p>
                        <?php endif; ?>
                        <form method="POST" action="<?php echo e(route('admin.review.resume', [$type, $subject->id])); ?>" class="mt-3">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="rounded-md border border-stone-300 px-3 py-1.5 text-xs font-medium hover:bg-stone-100">
                                Return to consideration
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="mt-5 space-y-6">
                        
                        <form method="POST" action="<?php echo e(route('admin.review.approve', [$type, $subject->id])); ?>"
                              class="border-b border-stone-100 pb-6">
                            <?php echo csrf_field(); ?>
                            <label class="block text-sm font-medium">Approve</label>
                            <p class="mt-1 text-xs text-stone-500">
                                A final judgement, recorded permanently. It does not invite anybody:
                                you issue the invitation separately, when you are ready.
                            </p>
                            <textarea name="rationale" rows="2" placeholder="Any note for the record (optional)"
                                      class="mt-2 w-full rounded-md border-stone-300 text-sm shadow-sm"></textarea>
                            <button type="submit" class="mt-2 rounded-md bg-stone-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-stone-700">
                                Approve
                            </button>
                        </form>

                        
                        <form method="POST" action="<?php echo e(route('admin.review.information', [$type, $subject->id])); ?>"
                              class="border-b border-stone-100 pb-6">
                            <?php echo csrf_field(); ?>
                            <label class="block text-sm font-medium">Need more information</label>
                            <p class="mt-1 text-xs text-stone-500">
                                A question, not a decision. The case stays open and you may ask again.
                                This goes to <?php echo e($recipient); ?>.
                                <?php if($type === 'referral'): ?>
                                    The person they recommended is not contacted.
                                <?php endif; ?>
                            </p>
                            <textarea name="required" rows="3" required placeholder="What would you like to know?"
                                      class="mt-2 w-full rounded-md border-stone-300 text-sm shadow-sm"></textarea>
                            <button type="submit" class="mt-2 rounded-md border border-stone-300 px-3 py-1.5 text-sm font-medium hover:bg-stone-50">
                                Send the question
                            </button>
                        </form>

                        
                        <form method="POST" action="<?php echo e(route('admin.review.set-aside', [$type, $subject->id])); ?>"
                              class="border-b border-stone-100 pb-6">
                            <?php echo csrf_field(); ?>
                            <label class="block text-sm font-medium">Set aside</label>
                            <p class="mt-1 text-xs text-stone-500">
                                A pause, for your own use. Nothing is sent, nothing is decided, and this
                                stays in the queue where you will see it.
                            </p>
                            <textarea name="reason" rows="2" placeholder="Why, for your own memory later (optional)"
                                      class="mt-2 w-full rounded-md border-stone-300 text-sm shadow-sm"></textarea>
                            <button type="submit" class="mt-2 rounded-md border border-stone-300 px-3 py-1.5 text-sm font-medium hover:bg-stone-50">
                                Set aside
                            </button>
                        </form>

                        
                        <form method="POST" action="<?php echo e(route('admin.review.decline', [$type, $subject->id])); ?>">
                            <?php echo csrf_field(); ?>
                            <label class="block text-sm font-medium">Decline</label>
                            <p class="mt-1 text-xs text-stone-500">
                                A final judgement, recorded with your reasoning. Nothing is sent.
                                <?php if($type === 'referral'): ?>
                                    The referrer is not told.
                                <?php endif; ?>
                            </p>
                            <textarea name="rationale" rows="2" required placeholder="Your reasoning, for the record"
                                      class="mt-2 w-full rounded-md border-stone-300 text-sm shadow-sm"></textarea>
                            <button type="submit" class="mt-2 rounded-md border border-stone-300 px-3 py-1.5 text-sm font-medium text-stone-700 hover:bg-stone-50">
                                Decline
                            </button>
                        </form>
                    </div>
                <?php endif; ?>

                
                <?php $__currentLoopData = $subject->informationRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $informationRequest): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="mt-6 rounded-md bg-stone-50 p-4 text-sm">
                        <p class="text-xs uppercase tracking-wide text-stone-500">
                            Asked <?php echo e($informationRequest->requested_at->format('j F Y')); ?>

                        </p>
                        <p class="mt-1 whitespace-pre-line text-stone-700"><?php echo e($informationRequest->required); ?></p>

                        <?php if($informationRequest->responded_at): ?>
                            <p class="mt-3 border-t border-stone-200 pt-3 text-xs uppercase tracking-wide text-stone-500">Reply</p>
                            <p class="mt-1 whitespace-pre-line text-stone-700"><?php echo e($informationRequest->response); ?></p>
                        <?php else: ?>
                            <form method="POST" action="<?php echo e(route('admin.information.response', $informationRequest)); ?>" class="mt-3">
                                <?php echo csrf_field(); ?>
                                <textarea name="response" rows="2" required placeholder="Record the reply you received"
                                          class="w-full rounded-md border-stone-300 text-sm shadow-sm"></textarea>
                                <button type="submit" class="mt-2 rounded-md border border-stone-300 px-3 py-1.5 text-xs font-medium hover:bg-white">
                                    Record the reply
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </section>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        
        <?php if($blockingInvitation): ?>
            <section class="mt-8 rounded-md border border-stone-200 bg-white p-5">
                <h2 class="text-sm font-medium">Invitation</h2>
                <p class="mt-2 text-sm text-stone-600">
                    <?php if($blockingInvitation->status === \App\Enums\InvitationStatus::Accepted): ?>
                        This person has registered and is an admitted collaborator.
                    <?php else: ?>
                        An invitation is outstanding, issued <?php echo e($blockingInvitation->created_at->format('j F Y')); ?>

                        by <?php echo e($blockingInvitation->delivery_channel->label()); ?>.
                        It lapses on <?php echo e($blockingInvitation->expires_at->format('j F Y')); ?>.
                    <?php endif; ?>
                </p>

                <?php if($blockingInvitation->status !== \App\Enums\InvitationStatus::Accepted): ?>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <form method="POST" action="<?php echo e(route('admin.invitations.reissue', $blockingInvitation)); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="rounded-md border border-stone-300 px-3 py-1.5 text-sm font-medium hover:bg-stone-50">
                                Issue a fresh link
                            </button>
                        </form>
                        <form method="POST" action="<?php echo e(route('admin.invitations.revoke', $blockingInvitation)); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="rounded-md border border-stone-300 px-3 py-1.5 text-sm font-medium text-stone-700 hover:bg-stone-50">
                                Withdraw the invitation
                            </button>
                        </form>
                    </div>
                    <p class="mt-2 text-xs text-stone-500">
                        A fresh link retires the previous one immediately.
                    </p>
                <?php endif; ?>
            </section>
        <?php elseif($approvedSubject): ?>
            <section class="mt-8 rounded-md border border-stone-200 bg-white p-5">
                <h2 class="text-sm font-medium">Invitation</h2>
                <p class="mt-2 text-sm text-stone-600">
                    This person has been approved. You can invite them whenever you are ready.
                </p>
                <a href="<?php echo e(route('admin.invitations.create-for-case', $case)); ?>"
                   class="mt-4 inline-block rounded-md bg-stone-800 px-3 py-2 text-sm font-medium text-white hover:bg-stone-700">
                    Generate invitation
                </a>
            </section>
        <?php endif; ?>

        
        <section class="mt-8">
            <h2 class="text-sm font-medium uppercase tracking-wide text-stone-500">History</h2>
            <ol class="mt-3 space-y-4 border-l border-stone-200 pl-5">
                <?php $__currentLoopData = $history; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="relative">
                        <span class="absolute -left-[1.4rem] top-1.5 h-2 w-2 rounded-full <?php echo e($entry->isDecision ? 'bg-stone-800' : 'bg-stone-300'); ?>"></span>
                        <p class="text-sm <?php echo e($entry->isDecision ? 'font-medium text-stone-800' : 'text-stone-700'); ?>">
                            <?php echo e($entry->summary); ?>

                        </p>
                        <p class="text-xs text-stone-500">
                            <?php echo e($entry->at->format('j F Y, H:i')); ?><?php if($entry->actor): ?> · <?php echo e($entry->actor); ?><?php endif; ?>
                        </p>
                        <?php if($entry->detail): ?>
                            <p class="mt-1 whitespace-pre-line text-sm text-stone-600"><?php echo e($entry->detail); ?></p>
                        <?php endif; ?>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ol>
        </section>
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
<?php /**PATH C:\Users\Muhammad Ashhad\Downloads\project longevity\active-longevity-portal\resources\views/admin/cases/show.blade.php ENDPATH**/ ?>