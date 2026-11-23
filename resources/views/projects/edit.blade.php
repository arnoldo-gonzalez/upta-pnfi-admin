<x-app-layout>
    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center space-x-4">
            <a href="{{ route('projects.index') }}" class="text-gray-500 hover:text-upta-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <x-heading level="h1" class="mb-0">{{ __('Edit Project') }}</x-heading>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6 border border-gray-200">
            <form action="{{ route('projects.update', $project) }}" method="POST">
                @csrf
                @method('PUT')

                @if($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                        <ul class="list-disc pl-5 text-sm space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">{{ __('Project Title') }}</label>
                    <input type="text" name="title" value="{{ old('title', $project->title) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500" required>
                </div>

                @if(isset($thematicAxis))
                    <div class="mb-4 bg-upta-50 border border-upta-200 p-4 rounded-md">
                        <h4 class="text-sm font-bold text-upta-900">{{ __('Thematic Axis:') }} {{ $thematicAxis->name }}</h4>
                        <p class="text-xs text-upta-700 mt-1">{{ __('The investigation lines below are restricted to this thematic axis.') }}</p>
                    </div>
                @endif

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">{{ __('Investigation Line') }}</label>
                    <select name="investigation_line_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500" required>
                        <option value="">{{ __('Select a line') }}</option>
                        @foreach ($investigationLines as $line)
                            <option value="{{ $line->id }}" {{ old('investigation_line_id', $project->investigation_line_id) == $line->id ? 'selected' : '' }}>{{ $line->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700">{{ __('Justification') }}</label>
                    <textarea name="justification" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500" required>{{ old('justification', $project->justification) }}</textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-upta-600 text-white px-6 py-2 rounded-md font-semibold hover:bg-upta-700 transition shadow-sm">
                        {{ __('Update Project') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
