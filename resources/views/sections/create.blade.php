<x-app-layout>
    <div class="max-w-3xl mx-auto py-6 sm:px-6 lg:px-8">
        
        <div class="mb-6 flex items-center space-x-4">
            <a href="{{ route('sections.index') }}" class="text-gray-500 hover:text-upta-600 pt-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <x-heading level="h1" class="mb-0">{{ __('Create Section') }}</x-heading>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6 border border-gray-200">
            <form action="{{ route('sections.store') }}" method="POST">
                @csrf

                <!-- Nombre de la sección -->
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Section Name') }}</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500" 
                           placeholder="{{ __('E.g., Section 3') }}" required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Estudiantes por grupo -->
                <div class="mb-4">
                    <label for="max_team_size" class="block text-sm font-medium text-gray-700">{{ __('Max students for team') }}</label>
                    <input type="number" name="max_team_size" id="max_team_size" value="{{ old('max_team_size') }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500" 
                           placeholder="1, 2, 3, ..." required>
                    @error('max_team_size')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Trayecto -->
                <div class="mb-6">
                    <label for="trayecto_id" class="block text-sm font-medium text-gray-700">{{ __('Trayecto') }}</label>
                    <select name="trayecto_id" id="trayecto_id" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500" required>
                        <option value="">{{ __('Select a Trayecto') }}</option>
                        @foreach ($trayectos as $trayecto)
                            <option value="{{ $trayecto->id }}" {{ old('trayecto_id') == $trayecto->id ? 'selected' : '' }}>
                                {{ $trayecto->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('trayecto_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Asesor -->
                <div class="mb-6">
                    <label for="teacher_id" class="block text-sm font-medium text-gray-700">{{ __('Advisor (Teacher)') }}</label>
                    <select name="teacher_id" id="teacher_id" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500">
                        <option value="">{{ __('Select an Advisor (Optional)') }}</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->name }} (CI: {{ $teacher->credential }})
                            </option>
                        @endforeach
                    </select>
                    @error('teacher_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Botón Guardar -->
                <div class="flex justify-end">
                    <button type="submit" class="bg-upta-600 text-white px-6 py-2 rounded-md font-semibold hover:bg-upta-700 transition shadow-sm">
                        {{ __('Save Section') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>