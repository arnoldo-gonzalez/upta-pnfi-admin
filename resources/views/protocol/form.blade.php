<x-app-layout>
    <div class="max-w-3xl mx-auto py-6 sm:px-6 lg:px-8">

        @if ($noTemplate ?? false)
            <div class="bg-white shadow-md rounded-lg p-8 border border-gray-200 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <h2 class="mt-4 text-lg font-semibold text-gray-800">{{ __('No hay plantilla de protocolo configurada') }}</h2>
                <p class="mt-2 text-sm text-gray-500">{{ __('Comuníquese con la coordinación para que configure la plantilla.') }}</p>
            </div>
        @else

            <div class="mb-6 flex items-center space-x-4">
                <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-upta-600 pt-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <x-heading level="h1" class="mb-0">{{ __('Llenar Protocolo') }}</x-heading>
            </div>

            <div class="bg-white shadow-md rounded-lg p-6 border border-gray-200">
                <p class="text-sm text-gray-600 mb-6">{{ __('Complete los campos del protocolo y previsualice el resultado.') }}</p>

                @if (!empty($project))
                    <input type="hidden" name="project_id" value="{{ $project->id }}">
                @endif

                @php
    $formAction = !empty($project) ? route('projects.protocol.preview', $project) : route('protocolo.llenar.preview');
@endphp

<form action="{{ $formAction }}" method="POST" class="space-y-5">
                    @csrf

                    @php $placeholders = $template->schema_json ?? []; @endphp

                    @foreach ($placeholders as $campo)
                        @php
                            $key = $campo['key'];
                            $label = $campo['label'] ?? $key;
                            $type = $campo['type'] ?? 'text';
                            $required = !empty($campo['required']);
                            $maxChars = $campo['maxChars'] ?? null;
                            $options = $campo['options'] ?? [];
                            $oldValue = old($key);
                            $value = !empty($values[$key]) ? $values[$key] : ($oldValue ?? '');
                        @endphp

                        <div>
                            <label for="{{ $key }}" class="block text-sm font-medium text-gray-700">
                                {{ $label }}@if ($required) <span class="text-red-600">*</span> @endif
                            </label>

                            @if ($type === 'textarea')
                                <textarea name="{{ $key }}" id="{{ $key }}"
                                          @if ($required) required @endif
                                          @if ($maxChars) maxlength="{{ $maxChars }}" @endif
                                          rows="3"
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500">{{ $value }}</textarea>
                            @elseif ($type === 'select')
                                <select name="{{ $key }}" id="{{ $key }}"
                                        @if ($required) required @endif
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500">
                                    <option value="">{{ __('-- Seleccionar --') }}</option>
                                    @foreach ($options as $opt)
                                        <option value="{{ $opt }}" {{ $value == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            @elseif ($type === 'date')
                                <input type="date" name="{{ $key }}" id="{{ $key }}"
                                       value="{{ $value }}"
                                       @if ($required) required @endif
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500">
                            @elseif ($type === 'number')
                                <input type="number" name="{{ $key }}" id="{{ $key }}"
                                       value="{{ $value }}"
                                       @if ($required) required @endif
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500">
                            @elseif ($type === 'email')
                                <input type="email" name="{{ $key }}" id="{{ $key }}"
                                       value="{{ $value }}"
                                       @if ($required) required @endif
                                       @if ($maxChars) maxlength="{{ $maxChars }}" @endif
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500">
                            @elseif ($type === 'tel')
                                <input type="tel" name="{{ $key }}" id="{{ $key }}"
                                       value="{{ $value }}"
                                       @if ($required) required @endif
                                       @if ($maxChars) maxlength="{{ $maxChars }}" @endif
                                       placeholder="+58 412 123 4567"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500">
                            @else
                                <input type="text" name="{{ $key }}" id="{{ $key }}"
                                       value="{{ $value }}"
                                       @if ($required) required @endif
                                       @if ($maxChars) maxlength="{{ $maxChars }}" @endif
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500">
                            @endif

                            @error($key)
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach

                    <div class="flex justify-end gap-3 pt-4">
                        <button type="submit" class="bg-white text-upta-700 px-6 py-2 rounded-md font-semibold hover:bg-upta-100 transition shadow-sm border border-upta-300">
                            {{ __('Previsualizar protocolo') }}
                        </button>

                        @if (!empty($project))
                            <button type="submit"
                                    formaction="{{ route('projects.submitProtocol', $project) }}"
                                    class="bg-green-600 text-white px-6 py-2 rounded-md font-semibold hover:bg-green-700 transition shadow-sm"
                                    onclick="return confirm('{{ __('¿Guardar y enviar el protocolo a revisión? Una vez enviado no podrá editarlo hasta que sea evaluado.') }}')">
                                {{ __('Guardar y enviar a revisión') }}
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        @endif
    </div>
</x-app-layout>