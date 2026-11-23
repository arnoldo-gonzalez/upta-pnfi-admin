<x-app-layout>
    <div class="max-w-3xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center space-x-4">
            <a href="{{ route('evaluations.coordinator.index') }}" class="text-gray-500 hover:text-upta-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <x-heading level="h1" class="mb-0">{{ __('Assign Evaluators') }}</x-heading>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6 border border-gray-200">
            <h2 class="text-lg font-bold text-gray-800">{{ $project->title }}</h2>
            <p class="text-sm text-gray-500 mb-4">{{ __('Evaluation stage:') }} <strong>{{ $stageName }}</strong></p>
            
            <form action="{{ route('evaluations.coordinator.assign.store', $project) }}" method="POST">
                @csrf
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Select Teachers') }}</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-64 overflow-y-auto p-2 border border-gray-200 rounded-md">
                        @foreach($teachers as $teacher)
                            <label class="inline-flex items-start p-2 rounded {{ $teacher->jury_available ? 'hover:bg-gray-50 cursor-pointer' : 'bg-gray-50 opacity-70 cursor-not-allowed' }}">
                                <input type="checkbox" name="teachers[]" value="{{ $teacher->id }}" 
                                    class="mt-0.5 rounded border-gray-300 text-upta-600 shadow-sm focus:ring-upta-500"
                                    {{ in_array($teacher->id, old('teachers', $assignedTeacherIds)) ? 'checked' : '' }}
                                    {{ $teacher->jury_available ? '' : 'disabled' }}>
                                <span class="ml-2 text-sm text-gray-700">
                                    <span class="block font-medium">{{ $teacher->name }}</span>
                                    <span class="block text-xs text-gray-500">{{ $teacher->specialty ?: __('Especialidad no registrada') }}</span>
                                    <span class="block text-xs {{ $teacher->jury_available ? 'text-green-700' : 'text-red-700' }}">
                                        {{ $teacher->jury_available ? ($teacher->availability ?: __('Disponible para jurado')) : __('No disponible para jurado') }}
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('teachers') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-upta-600 text-white px-6 py-2 rounded-md font-semibold hover:bg-upta-700 transition shadow-sm">
                        {{ __('Assign and Notify') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>