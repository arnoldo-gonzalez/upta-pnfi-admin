<x-app-layout>
    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('evaluations.teacher.index') }}" class="text-sm text-gray-500 hover:text-upta-600 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                {{ __('Back to list') }}
            </a>
        </div>

        <div class="bg-white shadow-md rounded-lg border border-gray-200 p-6 mb-6">
            <h2 class="text-2xl font-bold text-upta-900 mb-2">{{ $project->title }}</h2>
            <p class="text-sm text-gray-600 mb-4">{{ $project->justification }}</p>

            <!-- BLOQUE DEL DOCUMENTO -->
            <div class="bg-gray-50 p-4 rounded border border-gray-200 flex items-center mb-6">
                <svg class="h-8 w-8 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path></svg>
                <div>
                    <p class="text-sm font-medium text-gray-900">
                        {{ __('Protocol Document') }} - {{ __('Submission') }} #{{ $protocolDoc->submission_number ?? 1 }}
                    </p>
                    @if($protocolDoc)
                        <a href="{{ route('projects.protocol.download', $protocolDoc->project) }}" target="_blank" class="text-xs text-upta-600 hover:underline">
                            {{ __('Download Protocol Docx') }}
                        </a>
                    @else
                        <span class="text-xs text-red-500">{{ __('Document not found.') }}</span>
                    @endif
                </div>
            </div>

            <!-- MIEMBROS DEL EQUIPO -->
            <div class="border-t border-gray-100 pt-4">
                <h3 class="text-sm font-bold text-gray-800 mb-3 uppercase tracking-wider">{{ __('Team Members') }}</h3>
                <ul class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($project->students as $projectStudent)
                        <li class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="h-8 w-8 rounded-full bg-upta-600 flex items-center justify-center text-white font-bold text-sm mr-3">
                                {{ substr($projectStudent->enrollment->student->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $projectStudent->enrollment->student->name }}</p>
                                <p class="text-xs text-gray-500">{{ __('ID:') }} {{ $projectStudent->enrollment->student->credential }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
            
        </div>

        <!-- FORMULARIO DE EVALUACIÓN -->
        <div class="bg-white shadow-md rounded-lg border border-gray-200 p-6">
            <form action="{{ route('evaluations.teacher.store', $evaluation) }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Decision') }}</label>
                    <div class="flex space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="decision" value="approved" class="text-green-600 focus:ring-green-500" required>
                            <span class="ml-2 text-sm font-medium text-green-700">{{ __('Approve') }}</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="decision" value="rejected" class="text-red-600 focus:ring-red-500" required>
                            <span class="ml-2 text-sm font-medium text-red-700">{{ __('Reject with observations') }}</span>
                        </label>
                    </div>
                    @error('decision') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700">{{ __('Observations') }}</label>
                    <textarea name="observations" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500" placeholder="{{ __('Required if rejected...') }}">{{ old('observations') }}</textarea>
                    @error('observations') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- CORRECCIONES POR CAMPO -->
                @if(count($answers ?? []))
                    <div class="mb-6 border-t border-gray-200 pt-6">
                        <h4 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-wider">{{ __('Field Corrections') }}</h4>
                        <p class="text-xs text-gray-500 mb-3">{{ __('Enter a correction for each field you want the student to fix. Leave blank for no correction.') }}</p>
                        <div class="space-y-3">
                            @foreach($answers as $key => $value)
                                <div class="flex flex-col sm:flex-row gap-3 p-3 bg-gray-50 rounded border border-gray-100">
                                    <div class="sm:w-1/3">
                                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ $labels[$key] ?? $key }}</label>
                                        <input type="text" name="field_corrections[{{ $key }}]"
                                               value="{{ old('field_corrections.' . $key) ?? ($fieldCorrections[$key] ?? '') }}"
                                               placeholder="{{ __('Enter correction...') }}"
                                               class="w-full text-sm rounded border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500">
                                        <p class="text-xs text-gray-400 mt-1">{{ __('Student: ') }} "{{ e($value) }}"</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex justify-end">
                    <button type="submit" class="bg-upta-600 text-white px-6 py-2 rounded-md font-semibold hover:bg-upta-700 transition shadow-sm">
                        {{ __('Submit Evaluation') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>