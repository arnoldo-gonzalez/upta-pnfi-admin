@use('App\Enums\UserStatus')

<x-app-layout>
    <div class="max-w-5xl mx-auto py-6 sm:px-6 lg:px-8">

        @if(count($receivedInvitations) > 0)
            <div class="mb-6 bg-yellow-50 border border-yellow-200 shadow-md rounded-lg p-6">
                <h3 class="text-lg font-bold text-yellow-800 mb-4">{{ __('Pending Invitations') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($receivedInvitations as $invite)
                        <div class="bg-white p-4 rounded border border-yellow-100 shadow-sm flex flex-col justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">{{ __('Invitation to join:') }}</p>
                                <p class="text-base font-bold text-gray-900">{{ $invite->project->title }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ __('Sent by:') }} {{ $invite->sender->student->name }}</p>
                            </div>
                            <div class="flex space-x-2 mt-4">
                                <form action="{{ route('invitations.accept', $invite) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full bg-green-500 text-white text-sm px-3 py-2 rounded hover:bg-green-600 transition">{{ __('Accept') }}</button>
                                </form>
                                <form action="{{ route('invitations.reject', $invite) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full bg-red-500 text-white text-sm px-3 py-2 rounded hover:bg-red-600 transition">{{ __('Reject') }}</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if (!$project)
            <div class="text-center bg-white p-12 rounded-lg shadow-sm border border-gray-200">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('No Project Found') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('You are not registered in any project yet. You can create one or wait for an invitation.') }}</p>
                <div class="mt-6">
                    <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2 bg-upta-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-upta-700 focus:outline-none transition ease-in-out duration-150">
                        {{ __('Register My Project') }}
                    </a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <div class="md:col-span-2 space-y-6">
                    <div class="bg-white shadow-md rounded-lg border border-gray-200 p-6">
                        <div class="flex justify-between items-start mb-4">
                            <h2 class="text-2xl font-bold text-upta-900">{{ $project->title }}</h2>
                            @if($project->status_id === UserStatus::DRAFT->value)
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-semibold">{{ __('Draft') }}</span>
                            @elseif($project->status_id === UserStatus::PENDING->value)
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">{{ __('In Review') }}</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-600 mb-2"><strong>{{ __('Line:') }}</strong> {{ $project->investigationLine->name }}</p>
                        <p class="text-sm text-gray-600"><strong>{{ __('Justification:') }}</strong> {{ $project->justification }}</p>
                    </div>

                    <!-- Team Members -->
                    <div class="bg-white shadow-md rounded-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">{{ __('Team Members') }} ({{ count($teamMembers) }}/{{ $project->section->max_team_size }})</h3>
                        <ul class="divide-y divide-gray-100">
                            @foreach($teamMembers as $member)
                                <li class="py-3 flex justify-between items-center">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $member->enrollment->student->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $member->enrollment->student->email }}</p>
                                    </div>
                                    @if($member->enrollment_id === $enrollment->id)
                                        <span class="text-xs text-upta-600 font-bold">{{ __('You') }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Columna Lateral (Acciones e Invitaciones) -->
                <div class="space-y-6">

                    @php
                        $pendingCount = $sentInvitations->where('status_id', UserStatus::PENDING->value)->count();
                        $totalOccupied = count($teamMembers) + $pendingCount;
                        $hasSpace = $totalOccupied < $project->section->max_team_size;
                    @endphp

                    <!-- 1. FORMULARIO DE SUBIDA (Solo en Borrador) -->
                    @if($project->status_id === UserStatus::DRAFT->value)
                        <div class="bg-upta-50 border border-upta-200 shadow-md rounded-lg p-6">
                            <h3 class="text-sm font-bold text-upta-900 mb-3 uppercase tracking-wider">{{ __('Submit Protocol') }}</h3>
                            <p class="text-xs text-upta-700 mb-4">{{ __('Once your team is ready, upload your protocol PDF. This will block further team changes.') }}</p>
                            
                            <form action="{{ route('projects.submitProtocol', $project) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <input type="file" name="protocol_file" accept=".pdf" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-upta-600 file:text-white hover:file:bg-upta-700" required>
                                    @error('protocol_file') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <button type="submit" class="w-full bg-upta-600 text-white text-sm font-semibold px-4 py-2 rounded hover:bg-upta-700 transition shadow-sm">
                                    {{ __('Submit and Lock Team') }}
                                </button>
                            </form>
                        </div>
                    @endif

                    <!-- 2. VER DOCUMENTO (Si ya fue enviado) -->
                    @if($project->status_id === UserStatus::PENDING->value)
                        @php
                            $protocolDoc = $project->documents()->whereHas('type', function($q) {
                                $q->where('name', 'Protocolo');
                            })->first();
                        @endphp
                        
                        @if($protocolDoc)
                            <div class="bg-white shadow-md rounded-lg border border-gray-200 p-6">
                                <h3 class="text-sm font-bold text-gray-800 mb-3 uppercase tracking-wider">{{ __('Your Protocol') }}</h3>
                                <div class="flex items-center p-3 bg-gray-50 rounded border border-gray-200">
                                    <svg class="h-8 w-8 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path></svg>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">{{ __('Protocol Document') }}</p>
                                        <a href="{{ asset('storage/' . $protocolDoc->file_path) }}" target="_blank" class="text-xs text-upta-600 hover:underline">
                                            {{ __('Download / View PDF') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                    
                    <!-- 3. Invitar Estudiantes -->
                    @if($project->status_id === UserStatus::DRAFT->value && $hasSpace)
                        <div class="bg-white shadow-md rounded-lg border border-gray-200 p-6">
                            <h3 class="text-sm font-bold text-gray-800 mb-3 uppercase tracking-wider">{{ __('Invite Classmates') }}</h3>
                            
                            <form action="{{ route('projects.invite') }}" method="POST">
                                @csrf
                                
                                <div class="mb-3 relative" 
                                    x-data="{
                                        open: false,
                                        search: '',
                                        selectedId: '',
                                        students: {{ Js::from($studentsJson) }}, 
                                        get filteredStudents() {
                                            if (this.search === '') return this.students;
                                            return this.students.filter(s => 
                                                s.name.toLowerCase().includes(this.search.toLowerCase()) || 
                                                s.credential.includes(this.search)
                                            );
                                        },
                                        selectStudent(id, name) {
                                            this.selectedId = id;
                                            this.search = name;
                                            this.open = false;
                                        }
                                    }" 
                                    @click.away="open = false">
                                    
                                    <div class="relative">
                                        <input 
                                            type="text" 
                                            x-model="search" 
                                            @focus="open = true"
                                            @keydown.escape="open = false"
                                            placeholder="{{ __('Search by name or ID...') }}" 
                                            class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500"
                                            autocomplete="off"
                                        >
                                        
                                        <input type="hidden" name="receiver_enrollment_id" x-model="selectedId" required>

                                        <ul x-show="open" 
                                            x-transition 
                                            class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm" 
                                            style="display: none;">
                                            
                                            <template x-for="student in filteredStudents" :key="student.id">
                                                <li @click="selectStudent(student.id, student.name)" 
                                                    class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-upta-50 text-gray-900">
                                                    <span x-text="student.name" class="font-medium"></span>
                                                    <span x-text="'(' + student.credential + ')'" class="text-gray-500 text-xs ml-1"></span>
                                                </li>
                                            </template>
                                            
                                            <li x-show="filteredStudents.length === 0" class="cursor-default select-none relative py-2 pl-3 pr-9 text-gray-500 text-sm">
                                                {{ __('No students found.') }}
                                            </li>
                                        </ul>
                                    </div>

                                    <button type="submit" 
                                            :disabled="!selectedId"
                                            class="mt-3 w-full bg-upta-600 text-white text-sm font-semibold px-4 py-2 rounded hover:bg-upta-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                        {{ __('Send Invitation') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                    <!-- 4. Invitaciones Enviadas -->
                    @if(count($sentInvitations) > 0)
                        <div class="bg-white shadow-md rounded-lg border border-gray-200 p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">{{ __('Sent Invitations') }}</h3>
                            <ul class="divide-y divide-gray-100">
                                @foreach($sentInvitations as $invitation)
                                    <li class="py-3 flex justify-between items-center">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $invitation->receiver->student->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $invitation->receiver->student->email }}</p>
                                        </div>
                                        
                                        <!-- Estado de la invitación -->
                                        @if($invitation->status_id === UserStatus::PENDING->value)
                                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">
                                                {{ __('Pending') }}
                                            </span>
                                        @elseif($invitation->status_id === UserStatus::ACTIVE->value)
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                                {{ __('Accepted') }}
                                            </span>
                                        @elseif($invitation->status_id === UserStatus::REJECTED->value)
                                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">
                                                {{ __('Rejected') }}
                                            </span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-app-layout>