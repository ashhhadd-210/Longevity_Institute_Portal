
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['size' => 'md']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['size' => 'md']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php
    $img = $size === 'lg' ? 'h-20 w-auto' : 'h-14 w-auto';
    $text = $size === 'lg' ? 'text-lg' : 'text-base';
?>
<a href="<?php echo e(auth()->check() ? route('home') : url('/')); ?>" class="flex items-center gap-4" aria-label="The Longevity Compass, Collaborator Portal">
    <img src="<?php echo e(asset('images/longevity-compass-logo.svg')); ?>" alt="The Longevity Compass" class="<?php echo e($img); ?>">
    <span class="border-l border-stone-300 pl-4 <?php echo e($text); ?> font-semibold tracking-tight text-stone-800">Collaborator Portal</span>
</a>
<?php /**PATH C:\Users\Muhammad Ashhad\Downloads\04_Projects_Code\Longevity_Institute_Portal\active-longevity-portal\resources\views/components/portal-mark.blade.php ENDPATH**/ ?>