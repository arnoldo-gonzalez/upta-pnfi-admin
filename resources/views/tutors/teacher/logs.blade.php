@use('App\Enums\UserStatus')

<x-app-layout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <x-heading level="h1" class="mb-6">{{ __('Tutoring Logbook Reviews') }}</x-heading>

        <div class="space-y-8">
            @forelse($projects as $project)
                <div class="bg-white shadow-md rounded-lg border border-gray-200 overflow-hidden">
                    <!-- Cabecera del Proyecto -->
                    <div class="bg-upta-50 px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-upta-900">{{ $project->title }}</h3>
                        <p class="text-sm text-gray-600">{{ $project->section->name }} - {{ $project->section->trayecto->name }}</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($project->students as $student)
                                <span class="text-xs bg-white border border-gray-200 px-2 py-1 rounded">{{ $student->enrollment->student->name }}</span>
                            @endforeach
                        </div>
                    </div>

                    <!-- Bitácoras -->
                    <div class="p-6">
                        @forelse($project->tutorLogs as $log)
                            <div class="mb-6 pb-6 border-b border-gray-100 last:border-0 last:mb-0 last:pb-0">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <p class="text-sm font-bold text-gray-800">{{ \Carbon\Carbon::parse($log->meeting_date)->format('d/m/Y') }}</p>
                                        <p class="text-xs text-gray-500">{{ __('Uploaded by') }}: {{ $log->student->name }}</p>
                                    </div>
                                    
                                    <!-- Estado -->
                                    @if($log->status_id === UserStatus::PENDING->value)
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">{{ __('Pending Review') }}</span>
                                    @elseif($log->status_id === UserStatus::ACTIVE->value)
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">{{ __('Approved') }}</span>
                                    @else
                                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">{{ __('Rejected') }}</span>
                                    @endif
                                </div>

                                <p class="text-sm text-gray-700 mb-2">{{ $log->description }}</p>
                                
                                @if($log->file_path)
                                    <a href="{{ route('tutor-logs.download', $log) }}" class="text-xs text-upta-600 hover:underline inline-flex items-center mb-3">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        {{ __('Download Attachment') }}
                                    </a>
                                @endif

                                @if($log->teacher_observations)
                                    <div class="bg-gray-50 p-2 rounded text-xs text-gray-600 italic">
                                        <strong>{{ __('Your observations') }}:</strong> {{ $log->teacher_observations }}
                                    </div>
                                @endif

                                <!-- Formulario de Revisión (Solo si está pendiente) -->
                                @if($log->status_id === UserStatus::PENDING->value)
                                    <form action="{{ route('tutor-logs.review', $log) }}" method="POST" class="mt-4 bg-gray-50 p-3 rounded border border-gray-200">
                                        @csrf
                                        <div class="mb-2">
                                            <textarea name="teacher_observations" rows="2" class="w-full text-sm rounded border-gray-300 focus:border-upta-500 focus:ring-upta-500" placeholder="{{ __('Observations (required if rejected)...') }}"></textarea>
                                        </div>
                                        <div class="flex space-x-2">
                                            <button type="submit" name="decision" value="approved" class="bg-green-600 text-white text-xs font-semibold px-4 py-2 rounded hover:bg-green-700 transition">
                                                {{ __('Approve Log') }}
                                            </button>
                                            <button type="submit" name="decision" value="rejected" class="bg-red-600 text-white text-xs font-semibold px-4 py-2 rounded hover:bg-red-700 transition">
                                                {{ __('Reject Log') }}
                                            </button>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 text-center">{{ __('No logs submitted yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            @empty
                <div class="bg-white p-12 rounded-lg shadow-sm border border-gray-200 text-center">
                    <p class="text-gray-500">{{ __('You do not have any projects assigned as tutor.') }}</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>