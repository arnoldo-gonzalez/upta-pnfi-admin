@use('App\Enums\UserStatus')

<x-app-layout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        
        <!-- Barra de Capacidad -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
            <div class="mb-4 md:mb-0">
                <x-heading level="h1" class="mb-0">{{ __('Tutoring Dashboard') }}</x-heading>
                <p class="text-sm text-gray-500 mt-1">
                    {{ __('Current capacity:') }} <strong>{{ auth()->user()->max_tutored_projects - auth()->user()->availableTutorSlots() }} / {{ auth()->user()->max_tutored_projects }}</strong>
                </p>
            </div>
            
            <span class="text-xs text-gray-500">
                {{ __('Your tutoring capacity is managed by the coordination.') }}
            </span>
        </div>

        <!-- ========================================== -->
        <!-- SECCIÓN 1: SOLICITUDES PENDIENTES -->
        <!-- ========================================== -->
        <h2 class="text-xl font-bold text-gray-800 mb-4">{{ __('Pending Requests') }}</h2>
        <div class="grid grid-cols-1 gap-6 mb-10">
            @forelse($requests as $project)
                <div class="bg-white shadow-md rounded-lg border border-gray-200 p-6 flex flex-col md:flex-row justify-between items-start md:items-center">
                    <div class="mb-4 md:mb-0">
                        <h3 class="text-xl font-bold text-upta-900">{{ $project->title }}</h3>
                        <p class="text-sm text-gray-600">{{ $project->section->name }} - {{ $project->section->trayecto->name }}</p>
                        
                        <div class="mt-3">
                            <p class="text-xs font-bold text-gray-500 uppercase">{{ __('Team Members') }}</p>
                            <ul class="flex flex-wrap gap-2 mt-1">
                                @foreach($project->students as $student)
                                    <li class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $student->enrollment->student->name }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    
                    <div class="flex space-x-3 w-full md:w-auto">
                        <form action="{{ route('tutors.reject', $project) }}" method="POST" class="flex-1 md:flex-none">
                            @csrf
                            <button type="submit" class="w-full bg-red-50 text-red-600 border border-red-200 px-4 py-2 rounded-md font-semibold hover:bg-red-100 transition">
                                {{ __('Reject') }}
                            </button>
                        </form>
                        <form action="{{ route('tutors.accept', $project) }}" method="POST" class="flex-1 md:flex-none">
                            @csrf
                            <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded-md font-semibold hover:bg-green-700 transition shadow-sm">
                                {{ __('Accept') }}
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="text-center bg-white p-8 rounded-lg shadow-sm border border-gray-200">
                    <p class="text-gray-500">{{ __('You have no pending tutoring requests.') }}</p>
                </div>
            @endforelse
            @if($requests->hasPages())
                <div class="mt-4">{{ $requests->links() }}</div>
            @endif
        </div>

        <!-- ========================================== -->
        <!-- SECCIÓN 2: MIS PROYECTOS EN TUTORÍA (EVALUACIÓN) -->
        <!-- ========================================== -->
        <h2 class="text-xl font-bold text-gray-800 mb-4">{{ __('My Active Projects') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($activeProjects as $project)
                <div class="bg-white shadow-md rounded-lg border border-gray-200 p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-lg font-bold text-upta-900">{{ $project->title }}</h3>
                            
                            <!-- Etiqueta de Estado de Evaluación -->
                            @if($project->tutor_evaluation_status_id === UserStatus::PENDING->value)
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-[10px] font-bold uppercase">{{ __('Pending Evaluation') }}</span>
                            @elseif($project->tutor_evaluation_status_id === UserStatus::ACTIVE->value)
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-[10px] font-bold uppercase">{{ __('Approved') }}</span>
                            @elseif($project->tutor_evaluation_status_id === UserStatus::REJECTED->value)
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-[10px] font-bold uppercase">{{ __('Rejected') }}</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-600 mb-4">{{ $project->section->name }} - {{ $project->section->trayecto->name }}</p>
                        
                        <!-- Si ya fue evaluado, mostrar notas -->
                        @if($project->tutor_evaluation_status_id !== UserStatus::PENDING->value)
                            <div class="bg-gray-50 rounded p-3 mb-4 text-sm border border-gray-100">
                                <div class="flex justify-between mb-1">
                                    <span class="text-gray-600">{{ __('Written Report') }}:</span>
                                    <span class="font-bold">{{ $project->written_score }}%</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">{{ __('Technical Dev') }}:</span>
                                    <span class="font-bold">{{ $project->technical_score }}%</span>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Botón de Evaluación -->
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        @if($project->status_id === UserStatus::ACTIVE->value && $project->tutor_evaluation_status_id === UserStatus::PENDING->value)
                            <a href="{{ route('tutor-evaluations.create', $project) }}" class="block text-center w-full bg-upta-600 text-white px-4 py-2 rounded-md font-semibold hover:bg-upta-700 transition shadow-sm">
                                {{ __('Evaluate Project') }}
                            </a>
                        @elseif($project->status_id === UserStatus::READY_FOR_EXPERTS->value)
                            <span class="block text-center w-full bg-purple-50 text-purple-700 px-4 py-2 rounded-md font-semibold border border-purple-200">
                                {{ __('Ready for Experts') }}
                            </span>
                        @elseif($project->status_id === UserStatus::NEEDS_CORRECTIONS->value)
                             <span class="block text-center w-full bg-red-50 text-red-700 px-4 py-2 rounded-md font-semibold border border-red-200">
                                {{ __('Returned to Student') }}
                            </span>
                        @else
                             <span class="block text-center w-full bg-gray-50 text-gray-500 px-4 py-2 rounded-md font-semibold border border-gray-200">
                                {{ __('Waiting for Student Submission') }}
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-1 md:col-span-2 text-center bg-white p-8 rounded-lg shadow-sm border border-gray-200">
                    <p class="text-gray-500">{{ __('You are not currently tutoring any projects.') }}</p>
                </div>
            @endforelse
        </div>

    </div>
</x-app-layout>