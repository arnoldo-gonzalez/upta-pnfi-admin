@use('App\Enums\UserStatus')
@use('App\Enums\TutorRequestStatus')

<x-app-layout>
    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        
        <x-heading level="h1" class="mb-6">{{ __('My Academic Tutor') }}</x-heading>

        @if(!$project)
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 text-center">
                <p class="text-gray-500">{{ __('You do not have an active project yet.') }}</p>
            </div>
        @else
            <!-- SECCIÓN 1: Estado del Tutor -->
            <div class="bg-white shadow-md rounded-lg border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">{{ __('Tutor Status') }}</h3>
                
                @if($project->tutor_request_status_id === TutorRequestStatus::PENDING->value)
                    <div class="bg-yellow-50 p-4 rounded border border-yellow-200 text-sm text-yellow-800 flex items-center">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div>
                            <p class="font-bold">{{ __('Request Pending') }}</p>
                            <p class="mt-1">{{ __('Waiting for') }} <strong>{{ $project->tutor->name }}</strong> {{ __('to accept your request.') }}</p>
                        </div>
                    </div>
                @elseif($project->tutor_request_status_id === TutorRequestStatus::ACCEPTED->value)
                    <div class="bg-green-50 p-4 rounded border border-green-200 text-sm text-green-800 flex items-center">
                        <svg class="w-8 h-8 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div>
                            <p class="font-bold text-base">{{ __('Tutor Assigned') }}</p>
                            <p class="mt-1">{{ $project->tutor->name }} ({{ __('ID:') }} {{ $project->tutor->credential }})</p>
                        </div>
                    </div>
                @else
                    @if($project->section->is_active)
                        <p class="text-sm text-gray-600 mb-4">{{ __('Select a teacher to be your academic tutor. (Note: Your subject advisor cannot be your tutor).') }}</p>
                        
                        <form action="{{ route('tutors.request', $project) }}" method="POST">
                            @csrf
                            
                            @php
                                $teachersJson = $availableTeachers->map(function($t) {
                                    return ['id' => $t->id, 'name' => $t->name];
                                });
                            @endphp

                            <div class="relative mb-3" 
                                x-data="{
                                    open: false,
                                    search: '',
                                    teacherId: '',
                                    teachers: @js($teachersJson), 
                                    get filteredTeachers() {
                                        if (this.search === '') return this.teachers;
                                        return this.teachers.filter(t => t.name.toLowerCase().includes(this.search.toLowerCase()));
                                    },
                                    selectTeacher(id, name) {
                                        this.teacherId = id;
                                        this.search = name;
                                        this.open = false;
                                    }
                                }" 
                                @click.away="open = false">
                                
                                <input 
                                    type="text" 
                                    x-model="search" 
                                    @focus="open = true"
                                    @keydown.escape="open = false"
                                    placeholder="{{ __('Search by name...') }}" 
                                    class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500"
                                    autocomplete="off"
                                >
                                <input type="hidden" name="tutor_id" x-model="teacherId" required>

                                <ul x-show="open" x-transition class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm" style="display: none;">
                                    <template x-for="teacher in filteredTeachers" :key="teacher.id">
                                        <li @click="selectTeacher(teacher.id, teacher.name)" class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-upta-50 text-gray-900">
                                            <span x-text="teacher.name" class="font-medium"></span>
                                        </li>
                                    </template>
                                    <li x-show="filteredTeachers.length === 0" class="cursor-default select-none relative py-2 pl-3 pr-9 text-gray-500 text-sm">
                                        {{ __('No teachers found.') }}
                                    </li>
                                </ul>
                            </div>

                            <button type="submit" :disabled="!teacherId" class="w-full bg-upta-600 text-white text-sm font-semibold px-4 py-2 rounded hover:bg-upta-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                {{ __('Request Tutor') }}
                            </button>
                        </form>
                    @else
                        <div class="bg-red-50 p-4 rounded border border-red-200 text-sm text-red-800">
                            {{ __('The academic period is closed. You cannot request a tutor.') }}
                        </div>
                    @endif
                @endif
            </div>

            <!-- SECCIÓN 2: Bitácora de Tutorías (Solo si el tutor aceptó) -->
            @if($project->tutor_request_status_id === TutorRequestStatus::ACCEPTED->value)
                <div class="bg-white shadow-md rounded-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">{{ __('Tutoring Logbook') }}</h3>
                    
                    <!-- Formulario -->
                    @if($project->section->is_active)
                        <form action="{{ route('tutor-logs.store', $project) }}" method="POST" enctype="multipart/form-data" class="mb-8 bg-gray-50 p-4 rounded border border-gray-200">
                            @csrf
                            <h4 class="text-sm font-bold text-gray-700 mb-3">{{ __('Add new log') }}</h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                                <div>
                                    <label class="text-xs text-gray-600 block mb-1">{{ __('Meeting Date') }}</label>
                                    <input type="date" name="meeting_date" class="w-full text-sm rounded border-gray-300" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div>
                                    <label class="text-xs text-gray-600 block mb-1">{{ __('Attachment (Optional)') }}</label>
                                    <input type="file" name="file" accept=".pdf,.doc,.docx" class="w-full text-sm text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-upta-600 file:text-white hover:file:bg-upta-700">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="text-xs text-gray-600 block mb-1">{{ __('Description') }}</label>
                                <textarea name="description" rows="3" class="w-full text-sm rounded border-gray-300" placeholder="{{ __('What did you discuss?') }}" required></textarea>
                            </div>
                            
                            <button type="submit" class="bg-upta-600 text-white text-sm font-semibold px-6 py-2 rounded hover:bg-upta-700 transition">
                                {{ __('Save Log') }}
                            </button>
                        </form>
                    @endif

                    <!-- Timeline de Minutas -->
                    <div class="space-y-4 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-gray-200 before:to-transparent">
                        @forelse($tutorLogs as $log)
                            <div class="relative flex items-start space-x-4">
                                <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-full bg-upta-100 text-upta-700 font-bold text-xs z-10 border border-white">
                                    {{ \Carbon\Carbon::parse($log->meeting_date)->format('d/m') }}
                                </div>
                                <div class="flex-1 bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                                    <div class="flex justify-between items-start mb-2">
                                        <p class="text-sm text-gray-800">{{ $log->description }}</p>
                                        
                                        @if($log->status_id === UserStatus::PENDING->value)
                                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-[10px] font-bold uppercase tracking-wider">{{ __('Pending') }}</span>
                                        @elseif($log->status_id === UserStatus::ACTIVE->value)
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-[10px] font-bold uppercase tracking-wider">{{ __('Approved') }}</span>
                                        @else
                                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-[10px] font-bold uppercase tracking-wider">{{ __('Rejected') }}</span>
                                        @endif
                                    </div>
                                    
                                    @if($log->file_path)
                                        <a href="{{ route('tutor-logs.download', $log) }}" class="text-xs text-upta-600 hover:underline mt-2 inline-block font-medium">
                                            {{ __('Download Attachment') }}
                                        </a>
                                    @endif

                                    @if($log->teacher_observations)
                                        <div class="mt-3 p-2 bg-gray-50 rounded border border-gray-100 text-xs text-gray-700">
                                            <strong class="text-upta-700">{{ __('Tutor Observations') }}:</strong> 
                                            <span class="italic">"{{ $log->teacher_observations }}"</span>
                                        </div>
                                    @endif
                                    
                                    <p class="text-xs text-gray-400 mt-3">
                                        {{ __('Uploaded by') }}: {{ $log->student->name }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 text-center py-6">{{ __('No tutoring logs yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            @endif
        @endif
    </div>
</x-app-layout>