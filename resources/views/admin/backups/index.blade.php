<x-app-layout>
    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <x-heading level="h1" class="mb-0">{{ __('Respaldos de la base de datos') }}</x-heading>
                <p class="text-sm text-gray-500 mt-1">{{ __('Los respaldos se guardan en el almacenamiento privado del servidor.') }}</p>
            </div>

            <form action="{{ route('admin.backups.store') }}" method="POST">
                @csrf
                <button type="submit" class="bg-upta-600 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-upta-700">
                    {{ __('Generar respaldo') }}
                </button>
            </form>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">{{ session('error') }}</div>
        @endif

        <div class="bg-white shadow-md rounded-lg border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-upta-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('Archivo') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('Tamaño') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('Fecha') }}</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-upta-900 uppercase">{{ __('Acciones') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($backups as $backup)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $backup['name'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ number_format($backup['size'] / 1024, 1) }} KB</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ \Carbon\Carbon::createFromTimestamp($backup['modified'])->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('admin.backups.download', $backup['name']) }}" class="text-upta-600 hover:text-upta-800 text-xs font-semibold">{{ __('Descargar') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500">{{ __('Todavía no hay respaldos generados.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
