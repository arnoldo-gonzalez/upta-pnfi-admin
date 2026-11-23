@use('App\Enums\UserStatus')
@use('App\Enums\TutorRequestStatus')

<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Saludo -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-upta-900">{{ __('Welcome back') }}, {{ auth()->user()->name }}</h1>
                <p class="text-sm text-gray-500">{{ __('Here is the summary of your academic activities.') }}</p>
            </div>

            <!-- ========================================== -->
            <!-- VISTA PARA COORDINADOR / ADMIN -->
            <!-- ========================================== -->
            @if(auth()->user()->hasAnyRole(['admin', 'coordinador']))
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <!-- Card 1 -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">{{ __('Total Projects') }}</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $data['total_projects'] }}</p>
                        </div>
                    </div>
                    
                    <!-- Card 2 -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">{{ __('In Review') }}</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $data['pending_projects'] }}</p>
                        </div>
                    </div>

                    <!-- Card 3 -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">{{ __('Approved Projects') }}</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $data['approved_projects'] }}</p>
                        </div>
                    </div>

                    <!-- Card 4 -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex items-center">
                        <div class="p-3 rounded-full bg-upta-100 text-upta-600 mr-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">{{ __('Active Sections') }}</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $data['active_sections'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Proyectos por Trayecto -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">{{ __('Projects by Track') }}</h3>
                        <ul class="space-y-3">
                            @foreach($data['projects_by_track'] as $track => $count)
                                <li class="flex justify-between items-center text-sm">
                                    <span class="text-gray-600">{{ $track }}</span>
                                    <span class="font-bold text-upta-700 bg-upta-50 px-2 py-1 rounded">{{ $count }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    
                    <!-- Actividades Recientes -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">{{ __('Recent Activities') }}</h3>
                        <ul class="space-y-4">
                            @forelse($data['recent_logs'] as $log)
                                <li class="flex items-start">
                                    <div class="flex-shrink-0 w-2 h-2 mt-1.5 rounded-full bg-upta-500 mr-3"></div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">{{ \Carbon\Carbon::parse($log->meeting_date)->format('d/m/Y') }} - {{ $log->project->title }}</p>
                                        <p class="text-xs text-gray-500">{{ __('Tutoring log uploaded by') }} {{ $log->student->name }}</p>
                                    </div>
                                </li>
                            @empty
                                <p class="text-sm text-gray-500">{{ __('No recent activities.') }}</p>
                            @endforelse
                        </ul>
                    </div>
                </div>
            @endif

            <!-- ========================================== -->
            <!-- VISTA PARA DOCENTES -->
            <!-- ========================================== -->
            @if(auth()->user()->hasRole('docente'))
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                        <div class="mx-auto w-12 h-12 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800">{{ __('Pending Evaluations') }}</h3>
                        <p class="text-3xl font-bold text-upta-900 my-2">{{ $data['pending_evaluations'] }}</p>
                        <a href="{{ route('evaluations.teacher.index') }}" class="text-sm text-upta-600 hover:underline">{{ __('Go to Evaluations') }}</a>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                        <div class="mx-auto w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800">{{ __('Tutoring Requests') }}</h3>
                        <p class="text-3xl font-bold text-upta-900 my-2">{{ $data['tutoring_requests'] }}</p>
                        <a href="{{ route('tutors.index') }}" class="text-sm text-upta-600 hover:underline">{{ __('Go to Requests') }}</a>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                        <div class="mx-auto w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800">{{ __('My Tutorados') }}</h3>
                        <p class="text-3xl font-bold text-upta-900 my-2">{{ $data['my_tutor_projects'] }}</p>
                        <a href="{{ route('tutor-logs.teacher') }}" class="text-sm text-upta-600 hover:underline">{{ __('Go to Logbooks') }}</a>
                    </div>
                </div>
            @endif

            <!-- ========================================== -->
            <!-- VISTA PARA ESTUDIANTES (CONTEXTUAL) -->
            <!-- ========================================== -->
            @if(auth()->user()->hasRole('estudiante'))
                
                <!-- CASO 1: NO TIENE PROYECTO -->
                @if(!$data['my_project'])
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex flex-col items-center justify-center text-center">
                            <div class="w-16 h-16 bg-upta-50 text-upta-600 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800">{{ __('Start your journey') }}</h3>
                            <p class="text-sm text-gray-500 mb-4">{{ __('You do not have a project yet. Create one or join a team.') }}</p>
                            <a href="{{ route('projects.index') }}" class="bg-upta-600 text-white px-6 py-2 rounded-md text-sm font-semibold hover:bg-upta-700 transition">
                                {{ __('Go to My Project') }}
                            </a>
                        </div>

                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex flex-col items-center justify-center text-center">
                            <div class="w-16 h-16 bg-yellow-50 text-yellow-600 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800">{{ __('Pending Invitations') }}</h3>
                            <p class="text-3xl font-bold text-upta-900 my-2">{{ $data['pending_invitations'] }}</p>
                            <a href="{{ route('projects.index') }}" class="text-sm text-upta-600 hover:underline">{{ __('View Invitations') }}</a>
                        </div>
                    </div>

                <!-- CASO 2: TIENE PROYECTO -->
                @else
                    @php $project = $data['my_project']; @endphp

                    <!-- Tarjeta Principal del Proyecto -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-xl font-bold text-upta-900">{{ $project->title }}</h3>
                                <p class="text-sm text-gray-500 mt-1">{{ $project->section->name }} - {{ $project->section->period }}</p>
                            </div>
                            
                            <!-- Estado Visual -->
                            @if($project->status_id === UserStatus::DRAFT->value)
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-semibold">{{ __('Draft') }}</span>
                            @elseif($project->status_id === UserStatus::PENDING->value)
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">{{ __('In Review') }}</span>
                            @elseif($project->status_id === UserStatus::NEEDS_CORRECTIONS->value)
                                <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">{{ __('Needs Corrections') }}</span>
                            @elseif($project->status_id === UserStatus::ACTIVE->value)
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">{{ __('Approved') }}</span>
                            @endif
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('projects.index') }}" class="text-sm text-upta-600 hover:underline font-medium">{{ __('View Project Details') }} &rarr;</a>
                        </div>
                    </div>

                    <!-- Acciones Contextuales -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Si está en Borrador o Correcciones -->
                        @if(in_array($project->status_id, [UserStatus::DRAFT->value, UserStatus::NEEDS_CORRECTIONS->value]))
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-bold text-gray-800 mb-2">{{ __('Action Required') }}</h3>
                                <p class="text-sm text-gray-500 mb-4">
                                    {{ $project->status_id === UserStatus::DRAFT->value ? __('Complete your team and submit your protocol.') : __('You have observations from the evaluators. Please correct and resubmit.') }}
                                </p>
                                <a href="{{ route('projects.index') }}" class="bg-upta-600 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-upta-700 transition">
                                    {{ __('Go to Project') }}
                                </a>
                            </div>
                        
                        <!-- Si está en Revisión -->
                        @elseif($project->status_id === UserStatus::PENDING->value)
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-bold text-gray-800 mb-2">{{ __('Waiting for Review') }}</h3>
                                <p class="text-sm text-gray-500">
                                    {{ __('Your protocol is currently being evaluated by the assigned teachers. You will be notified once there is a verdict.') }}
                                </p>
                            </div>

                        <!-- Si está Aprobado -->
                        @elseif($project->status_id === UserStatus::ACTIVE->value)
                            
                            <!-- Sin Tutor -->
                            @if($project->tutor_request_status_id !== TutorRequestStatus::ACCEPTED->value)
                                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 md:col-span-2 flex flex-col md:flex-row items-center justify-between">
                                    <div class="mb-4 md:mb-0">
                                        <h3 class="text-lg font-bold text-green-800">{{ __('Project Approved!') }}</h3>
                                        <p class="text-sm text-gray-500">{{ __('Now you need to select an academic tutor to guide your project.') }}</p>
                                    </div>
                                    <a href="{{ route('tutors.student.index') }}" class="bg-upta-600 text-white px-6 py-2 rounded-md text-sm font-semibold hover:bg-upta-700 transition whitespace-nowrap">
                                        {{ __('Find a Tutor') }}
                                    </a>
                                </div>
                            
                            <!-- Con Tutor -->
                            @else
                                <!-- Resumen de Bitácora -->
                                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                    <h3 class="text-lg font-bold text-gray-800 mb-2">{{ __('Tutoring Logbook') }}</h3>
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm text-gray-500">{{ __('Total logs submitted:') }}</p>
                                        <span class="text-2xl font-bold text-upta-900">{{ $data['tutor_logs_count'] }}</span>
                                    </div>
                                    @if($data['last_log'])
                                        <p class="text-xs text-gray-400 mt-2">{{ __('Last log') }}: {{ \Carbon\Carbon::parse($data['last_log']->meeting_date)->format('d/m/Y') }}</p>
                                    @endif
                                    <a href="{{ route('tutors.student.index') }}" class="inline-block mt-4 text-sm text-upta-600 hover:underline font-medium">{{ __('Manage Logbook') }} &rarr;</a>
                                </div>

                                <!-- Tarjeta del Tutor -->
                                @if($project->tutor)
                                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                        <h3 class="text-lg font-bold text-gray-800 mb-2">{{ __('My Tutor') }}</h3>
                                        <div class="flex items-center mt-3">
                                            <div class="h-10 w-10 rounded-full bg-upta-100 text-upta-700 flex items-center justify-center font-bold mr-3">
                                                {{ substr($project->tutor->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $project->tutor->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $project->tutor->email }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                        <h3 class="text-lg font-bold text-gray-800 mb-2">{{ __('My Tutor') }}</h3>
                                        <p class="text-sm text-gray-500">{{ __('Your tutor is no longer available. Please contact the coordination.') }}</p>
                                    </div>
                                @endif
                            @endif
                        @endif
                    </div>
                @endif
            @endif

            <!-- ========================================== -->
            <!-- CALENDARIO DE ACTIVIDADES (Para todos los roles) -->
            <!-- ========================================== -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Calendario Mensual -->
                <div class="md:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-800">{{ __('Activity Calendar') }}</h3>
                        <span class="text-sm font-semibold text-upta-600">{{ now()->translatedFormat('F Y') }}</span>
                    </div>

                    @php
                        // Lógica para generar el calendario del mes actual
                        $today = \Carbon\Carbon::now();
                        $startOfMonth = $today->copy()->startOfMonth();
                        $endOfMonth = $today->copy()->endOfMonth();
                        $startDayOfWeek = $startOfMonth->dayOfWeek; // 0 = Domingo, 1 = Lunes...
                        $daysInMonth = $today->daysInMonth;
                    @endphp

                    <!-- Días de la semana -->
                    <div class="grid grid-cols-7 gap-1 text-center mb-2">
                        @foreach(['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'] as $day)
                            <div class="text-xs font-bold text-gray-400 uppercase">{{ __($day) }}</div>
                        @endforeach
                    </div>

                    <!-- Cuadrícula del mes -->
                    <div class="grid grid-cols-7 gap-1">
                        <!-- Espacios vacíos antes del primer día -->
                        @for ($i = 0; $i < $startDayOfWeek; $i++)
                            <div class="h-10 sm:h-12 rounded-md bg-gray-50/50"></div>
                        @endfor

                        <!-- Días del mes -->
                        @for ($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $dateString = $today->copy()->setDay($day)->format('Y-m-d');
                                $hasEvent = isset($data['calendar_events'][$dateString]);
                                $isToday = $today->day === $day;
                            @endphp
                            
                            <div class="h-10 sm:h-12 rounded-md border flex flex-col items-center justify-center relative transition-colors
                                {{ $isToday ? 'bg-upta-50 border-upta-300' : 'bg-white border-gray-100' }}
                                {{ $hasEvent ? 'cursor-pointer hover:bg-gray-50' : '' }}">
                                
                                <span class="text-sm font-medium {{ $isToday ? 'text-upta-700 font-bold' : 'text-gray-700' }}">{{ $day }}</span>
                                
                                <!-- Puntito indicador de evento -->
                                @if($hasEvent)
                                    <span class="absolute bottom-1.5 w-1.5 h-1.5 rounded-full bg-upta-500"></span>
                                @endif
                            </div>
                        @endfor
                    </div>
                </div>

                <!-- Próximas Actividades (Lista) -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">{{ __('Upcoming Events') }}</h3>
                    
                    <div class="space-y-4">
                        @php
                            // Filtramos los eventos para mostrar solo los de hoy en adelante
                            $upcomingEvents = $events->filter(function($event) {
                                return \Carbon\Carbon::parse($event->start_date)->isToday() || \Carbon\Carbon::parse($event->start_date)->isFuture();
                            })->take(5);
                        @endphp

                        @forelse($upcomingEvents as $event)
                            @php
                                $userRole = auth()->user()->getRoleNames()->first();
                                $link = $event->role_links[$userRole] ?? null;
                                // Verificamos si la ruta realmente existe antes de usarla
                                $url = ($link && \Illuminate\Support\Facades\Route::has($link)) ? route($link) : null;
                            @endphp
                            
                            <!-- Si hay un enlace válido, envolvemos en <a>, sino en <div> -->
                            @if($url)
                                <a href="{{ $url }}" class="flex items-start group hover:bg-gray-50 p-2 rounded transition">
                            @else
                                <div class="flex items-start p-2">
                            @endif
                            
                                <div class="flex-shrink-0 w-10 h-10 bg-upta-100 text-upta-700 rounded-lg flex flex-col items-center justify-center mr-3">
                                    <span class="text-xs font-bold uppercase">{{ \Carbon\Carbon::parse($event->start_date)->format('M') }}</span>
                                    <span class="text-sm font-bold leading-none">{{ \Carbon\Carbon::parse($event->start_date)->format('d') }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-800 truncate w-40 sm:w-48 group-hover:text-upta-600 transition">{{ $event->title }}</p>
                                    <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($event->start_date)->format('h:i A') }}</p>
                                </div>

                            @if($url)
                                </a>
                            @else
                                </div>
                            @endif
                        @empty
                            <div class="text-center py-6">
                                <svg class="mx-auto h-8 w-8 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <p class="text-sm text-gray-500">{{ __('No upcoming events.') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>