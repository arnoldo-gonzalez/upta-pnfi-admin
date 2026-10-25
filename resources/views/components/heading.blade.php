@props(['level' => 'h2'])

@php
    $classes = match($level) {
        'h1' => 'text-3xl font-extrabold text-upta-900 tracking-tight mb-6',
        'h2' => 'text-2xl font-bold text-upta-800 mb-4',
        'h3' => 'text-xl font-semibold text-upta-700 mb-3',
        'h4' => 'text-lg font-medium text-gray-800 mb-2',
        'h5' => 'text-base font-medium text-gray-700 mb-2',
        'h6' => 'text-sm font-medium text-gray-600 mb-1',
        default => 'text-xl font-bold text-gray-800 mb-4',
    };
@endphp

<{{ $level }} {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</{{ $level }}>