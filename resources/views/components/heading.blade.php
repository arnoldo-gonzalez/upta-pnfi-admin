@props(['level' => 'h2'])

@php

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
@endphp

<{{ $level }} {{ $attributes->merge(['class' => $finalClasses]) }}>
    {{ $slot }}
</{{ $level }}>