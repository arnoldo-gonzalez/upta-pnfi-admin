<x-app-layout>
    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center space-x-4">
            <a href="{{ route('projects.index') }}" class="text-gray-500 hover:text-upta-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <x-heading level="h1" class="mb-0">{{ __('Register Project') }}</x-heading>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6 border border-gray-200">
            <form action="{{ route('projects.store') }}" method="POST">
                @csrf

                <!-- Título -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">{{ __('Project Title') }}</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500" required>
                    @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Eje Temático (Informativo) -->
                @if(isset($thematicAxis))
                <div class="mb-4 bg-upta-50 border border-upta-200 p-4 rounded-md flex items-start">
                    <svg class="w-5 h-5 text-upta-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h4 class="text-sm font-bold text-upta-900">{{ __('Thematic Axis:') }} {{ $thematicAxis->name }}</h4>
                        <p class="text-xs text-upta-700 mt-1">
                            {{ __('Your project must strictly align with this thematic axis based on your academic year. The investigation lines below are restricted to this framework.') }}
                        </p>
                    </div>
                </div>
                @endif

                <!-- Línea de Investigación -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">{{ __('Investigation Line') }}</label>
                    <select name="investigation_line_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500" required>
                        <option value="">{{ __('Select a line') }}</option>
                        @foreach ($investigationLines as $line)
                            <option value="{{ $line->id }}" {{ old('investigation_line_id') == $line->id ? 'selected' : '' }}>{{ $line->name }}</option>
                        @endforeach
                    </select>
                    @error('investigation_line_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Justificación -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700">{{ __('Justification') }}</label>
                    <textarea name="justification" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500" required>{{ old('justification') }}</textarea>
                    @error('justification') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-upta-600 text-white px-6 py-2 rounded-md font-semibold hover:bg-upta-700 transition shadow-sm">
                        {{ __('Create Project') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>