@props(['title'])

<div class="print-only mb-6 border-b-2 border-gray-800 pb-4">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold text-gray-900">UPTA - PNFI</h1>
            <p class="text-sm text-gray-600">Coordinación de Proyectos Socio-Tecnológicos</p>
        </div>
        <div class="text-right">
            <h2 class="text-lg font-bold text-gray-800">{{ __($title) }}</h2>
            <p class="text-sm text-gray-600">{{ __('Date') }}: {{ now()->format('d/m/Y') }}</p>
        </div>
    </div>
</div>