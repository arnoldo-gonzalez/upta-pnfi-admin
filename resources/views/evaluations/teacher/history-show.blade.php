<x-app-layout>
    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center space-x-4">
            <a href="{{ route('evaluations.teacher.history') }}" class="text-gray-500 hover:text-upta-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <x-heading level="h1" class="mb-0">{{ __('Timeline for') }}: {{ $project->title }}</x-heading>
        </div>

        <div class="bg-white shadow-md rounded-lg border border-gray-200 p-6 mb-6">
            <div class="space-y-6">
                @foreach($documents as $index => $doc)
                    <div class="relative pl-6 border-l-2 border-upta-200">
                        <!-- Puntito -->
                        <div class="absolute -left-[9px] top-1 h-4 w-4 rounded-full bg-upta-500 border-2 border-white"></div>
                        
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-bold text-gray-900">
                                    {{ __('Submission') }} #{{ $documents->count() - $index }}
                                </p>
                                <p class="text-xs text-gray-500">{{ $doc->created_at->format('d/m/Y h:i A') }}</p>
                                <a href="{{ route('documents.download', $doc) }}" target="_blank" class="text-xs text-upta-600 hover:underline mt-1 inline-block">
                                    {{ __('Download File') }} (.pdf)
                                </a>
                            </div>
                        </div>

                        <!-- Evaluaciones de este envío -->
                        @php
                            $evals = \App\Models\ProjectEvaluation::where('project_document_id', $doc->id)->with('teacher')->get();
                        @endphp
                        
                        @if($evals->count() > 0)
                            <div class="mt-3 space-y-2">
                                @foreach($evals as $eval)
                                    @php
                                        // Resaltar si la evaluación es del docente logueado
                                        $isMyEvaluation = $eval->user_id === auth()->id();
                                        $bgClass = $eval->status_id === \App\Enums\UserStatus::ACTIVE->value ? 'bg-green-50 text-green-800' : ($eval->status_id === \App\Enums\UserStatus::PENDING->value ? 'bg-yellow-50 text-yellow-800' : 'bg-red-50 text-red-800');
                                        $statusText = $eval->status_id === \App\Enums\UserStatus::ACTIVE->value ? __('Approved') : ($eval->status_id === \App\Enums\UserStatus::PENDING->value ? __('Pending') : __('Rejected'));
                                    @endphp
                                    
                                    <div class="text-xs p-3 rounded border {{ $bgClass }} {{ $isMyEvaluation ? 'border-2 border-upta-400 shadow-sm' : 'border-transparent' }}">
                                        <div class="flex justify-between items-center">
                                            <strong>
                                                {{ $eval->teacher->name }} 
                                                @if($isMyEvaluation) <span class="ml-1 text-[10px] bg-upta-200 text-upta-800 px-1.5 py-0.5 rounded">{{ __('You') }}</span> @endif
                                            </strong>
                                            <span class="font-bold">{{ $statusText }}</span>
                                        </div>
                                        
                                        @if($eval->observations)
                                            <div class="mt-2 text-gray-700 italic border-t border-black/5 pt-2">
                                                "{{ $eval->observations }}"
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>