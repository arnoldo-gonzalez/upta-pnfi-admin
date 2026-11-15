<x-app-layout>
    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center space-x-4">
            <a href="{{ route('tutors.index') }}" class="text-gray-500 hover:text-upta-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <x-heading level="h1" class="mb-0">{{ __('Evaluate Project') }}</x-heading>
        </div>

        <div class="bg-white shadow-md rounded-lg border border-gray-200 p-6 mb-6">
            <h2 class="text-xl font-bold text-upta-900">{{ $project->title }}</h2>
            <p class="text-sm text-gray-500">{{ $project->section->trayecto->name }} - {{ $project->section->name }}</p>
            @if($project->section->trayecto->number == 3)
                <div class="mt-2 bg-red-50 text-red-800 p-2 rounded text-xs font-bold border border-red-200">
                    {{ __('Note: For 3rd Year projects, the technical area requires a minimum of 95% to be approved.') }}
                </div>
            @endif
        </div>

        <form action="{{ route('tutor-evaluations.store', $project) }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Informe Escrito -->
                <div class="bg-white shadow-md rounded-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">{{ __('Written Report (Min 80%)') }}</h3>
                    <div class="space-y-4">
                        @foreach($writtenCriteria as $criteria)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ $criteria->name }} <span class="text-gray-400 text-xs">({{ $criteria->weight_percentage }}%)</span></label>
                                <input type="number" name="scores[{{ $criteria->id }}]" min="0" max="100" value="{{ old('scores.'.$criteria->id) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500" required>
                                @error('scores.'.$criteria->id) <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Desarrollo Técnico -->
                <div class="bg-white shadow-md rounded-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">
                        {{ __('Technical Development') }} 
                        <span class="text-sm font-normal text-gray-500">(Min {{ $project->section->trayecto->number == 3 ? '95' : '80' }}%)</span>
                    </h3>
                    <div class="space-y-4">
                        @foreach($technicalCriteria as $criteria)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ $criteria->name }} <span class="text-gray-400 text-xs">({{ $criteria->weight_percentage }}%)</span></label>
                                <input type="number" name="scores[{{ $criteria->id }}]" min="0" max="100" value="{{ old('scores.'.$criteria->id) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500" required>
                                @error('scores.'.$criteria->id) <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="bg-upta-600 text-white px-6 py-2 rounded-md font-semibold hover:bg-upta-700 transition shadow-sm">
                    {{ __('Save Evaluation') }}
                </button>
            </div>
        </form>
    </div>
</x-app-layout>