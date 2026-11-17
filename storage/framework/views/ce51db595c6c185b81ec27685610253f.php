<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['level' => 'h2']));

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

foreach (array_filter((['level' => 'h2']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php

    // Fix: Ahora si se pasa algun margen personalizado se toma en cuenta

    $baseClasses = match($level) {
        'h1' => 'text-3xl font-extrabold text-upta-900 tracking-tight',
        'h2' => 'text-2xl font-bold text-upta-800',
        'h3' => 'text-xl font-semibold text-upta-700',
        'h4' => 'text-lg font-medium text-gray-800',
        'h5' => 'text-base font-medium text-gray-700',
        'h6' => 'text-sm font-medium text-gray-600',
        default => 'text-xl font-bold text-gray-800',
    };

    $defaultMargin = match($level) {
        'h1' => 'mb-6',
        'h2' => 'mb-4',
        'h3' => 'mb-3',
        'h4' => 'mb-2',
        'h5' => 'mb-2',
        'h6' => 'mb-1',
        default => 'mb-4',
    };

    $userClasses = $attributes->get('class', '');
    $hasCustomMargin = str_contains($userClasses, 'mb-') || str_contains($userClasses, 'm-') || str_contains($userClasses, 'mt-');

    $finalClasses = $baseClasses . ' ' . ($hasCustomMargin ? '' : $defaultMargin);
?>

<<?php echo e($level); ?> <?php echo e($attributes->merge(['class' => $finalClasses])); ?>>
    <?php echo e($slot); ?>

</<?php echo e($level); ?>><?php /**PATH C:\Users\Sebas\Documents\University things\Proyecto 2do año\Proyecto_Código\resources\views/components/heading.blade.php ENDPATH**/ ?>