<x-app-layout>
    <div class="max-w-6xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center space-x-4">
            <a href="{{ route('coordinator.protocols') }}" class="text-gray-500 hover:text-upta-600 pt-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <x-heading level="h1" class="mb-0">{{ __('Plantilla de Protocolo') }}</x-heading>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6 border border-gray-200 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-2">{{ __('Subir plantilla (.docx)') }}</h2>
            <p class="text-sm text-gray-500 mb-4">
                {{ __('Escribe los placeholders en Word de un tirón, por ejemplo:') }}
                <code class="bg-gray-100 px-1 rounded">@{{NOMBRE}}</code>,
                <code class="bg-gray-100 px-1 rounded">@{{FECHA}}</code>.
            </p>

            <form action="{{ route('admin.protocolo.plantilla.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label for="protocol_template" class="block text-sm font-medium text-gray-700">{{ __('Archivo .docx') }}</label>
                    <input type="file" name="protocol_template" id="protocol_template" accept=".docx"
                           class="mt-1 block w-full text-sm text-gray-700 border border-gray-300 rounded-md p-2" required>
                    @error('protocol_template')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="bg-upta-600 text-white px-6 py-2 rounded-md font-semibold hover:bg-upta-700 transition shadow-sm">
                    {{ __('Subir y analizar plantilla') }}
                </button>
            </form>
        </div>

        @if ($template)
            <div class="bg-white shadow-md rounded-lg p-6 border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">{{ __('Variables detectadas') }}</h2>
                        <p class="text-sm text-gray-500">{{ $template->nombre }} — {{ count($template->schema_json ?? []) }} {{ __('variables') }}</p>
                    </div>
                </div>

                @php $placeholders = $template->schema_json ?? []; @endphp

                @if (count($placeholders))
                    <form action="{{ route('admin.protocolo.plantilla.schema') }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Key') }}</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Label visible') }}</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Tipo') }}</th>
                                        <th class="px-3 py-2 text-center font-medium text-gray-600">{{ __('Requerido') }}</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Max chars') }}</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Opciones (select)') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($placeholders as $i => $campo)
                                        @php
                                            $key = $campo['key'];
                                            $label = $campo['label'] ?? $key;
                                            $type = $campo['type'] ?? 'text';
                                            $required = !empty($campo['required']);
                                            $maxChars = $campo['maxChars'] ?? '';
                                            $options = implode(',', $campo['options'] ?? []);
                                        @endphp
                                        <tr x-data="{ type: @js($type) }">
                                            <td class="px-3 py-2 font-mono text-gray-700">
                                                {{ $key }}
                                                <input type="hidden" name="placeholders[{{ $i }}][key]" value="{{ $key }}">
                                            </td>
                                            <td class="px-3 py-2">
                                                <input type="text" name="placeholders[{{ $i }}][label]"
                                                       value="{{ $label }}"
                                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-upta-500 focus:ring-upta-500">
                                            </td>
                                            <td class="px-3 py-2">
                                                <select name="placeholders[{{ $i }}][type]" x-model="type"
                                                        class="rounded-md border-gray-300 shadow-sm text-sm focus:border-upta-500 focus:ring-upta-500">
                                                    @foreach (['text', 'textarea', 'email', 'tel', 'cedula', 'number', 'date', 'select'] as $tipo)
                                                        <option value="{{ $tipo }}" {{ $tipo === $type ? 'selected' : '' }}>{{ $tipo }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                <input type="hidden" name="placeholders[{{ $i }}][required]" value="0">
                                                <input type="checkbox" name="placeholders[{{ $i }}][required]" value="1"
                                                       {{ $required ? 'checked' : '' }}
                                                       class="rounded border-gray-300 text-upta-600 focus:ring-upta-500">
                                            </td>
                                            <td class="px-3 py-2">
                                                <input type="number" min="0" name="placeholders[{{ $i }}][maxChars]"
                                                       value="{{ $maxChars }}"
                                                       class="w-24 rounded-md border-gray-300 shadow-sm text-sm focus:border-upta-500 focus:ring-upta-500">
                                            </td>
                                            <td class="px-3 py-2">
                                                <input type="text" x-show="type === 'select'" x-cloak
                                                       name="placeholders[{{ $i }}][options]"
                                                       value="{{ $options }}"
                                                       placeholder="op1,op2,op3"
                                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-upta-500 focus:ring-upta-500">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end mt-6">
                            <button type="submit" class="bg-upta-600 text-white px-6 py-2 rounded-md font-semibold hover:bg-upta-700 transition shadow-sm">
                                {{ __('Guardar configuración') }}
                            </button>
                        </div>
                    </form>
                @else
                    <p class="text-sm text-gray-500">{{ __('La plantilla no contiene variables detectadas.') }}</p>
                @endif
            </div>
        @endif
    </div>

    <style>[x-cloak] { display: none !important; }</style>
</x-app-layout>