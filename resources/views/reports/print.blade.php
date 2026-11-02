<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __($title) }}</title>
    @vite(['resources/css/app.css'])
    <style>
        body { background: white; padding: 20px; font-family: sans-serif; }
        @media print {
            @page { margin: 1cm; }
            body { padding: 0; }
        }
        table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f3f4f6; font-weight: bold; }
    </style>
</head>
<body onload="window.print()">

    <div class="mb-6 border-b-2 border-gray-800 pb-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold">UPTA - PNFI</h1>
            <p class="text-sm text-gray-600">Coordinación de Proyectos Socio-Tecnológicos</p>
        </div>
        <div class="text-right">
            <h2 class="text-lg font-bold">{{ __($title) }}</h2>
            <p class="text-sm">{{ __('Date') }}: {{ now()->format('d/m/Y') }}</p>
        </div>
    </div>

    <!-- Aquí se inyecta la tabla específica -->
    @include($view, ['data' => $data])

</body>
</html>