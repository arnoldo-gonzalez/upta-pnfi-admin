<x-app-layout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <x-heading level="h1" class="mb-0">{{ __('Assign Tutors') }}</x-heading>
                <p class="text-sm text-gray-500 mt-1">{{ __('Manually assign a tutor to projects that were not accepted during the 3-week period.') }}</p>
            </div>
            
            <!-- Botón para calcular la cuota automáticamente -->
            <x-confirm-action 
                :action="route('coordinator.calculateQuota')" 
                method="POST"
                title="Calculate Fair Quota"
                message="This will divide the total unassigned projects by the total available teachers and set that as the new maximum limit for everyone. Continue?"
                confirmText="Calculate"
                icon="check"
                buttonClass="bg-gray-800 hover:bg-gray-700"
            >
                <button type="button" class="bg-gray-800 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-gray-900 transition shadow-sm flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    {{ __('Recalculate Quota') }}
                </button>
            </x-confirm-action>
        </div>

        @if(session('success')) <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('success') }}</div> @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Lista de Proyectos sin Tutor -->
            <div class="md:col-span-2 bg-white shadow-md rounded-lg border border-gray-200 overflow-hidden">
                <div class="bg-yellow-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-yellow-800">{{ __('Projects without Tutor') }} ({{ count($unassignedProjects) }})</h3>
                </div>
                
                <div class="divide-y divide-gray-200">
                    @forelse($unassignedProjects as $project)
                        <div class="p-6 flex flex-col md:flex-row justify-between items-start md:items-center">
                            <div class="mb-4 md:mb-0">
                                <h4 class="text-sm font-bold text-gray-900">{{ $project->title }}</h4>
                                <p class="text-xs text-gray-500">{{ $project->section->trayecto->name }} - {{ $project->section->name }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ __('Students:') }} {{ $project->students->pluck('enrollment.student.name')->implode(', ') }}</p>
                            </div>
                            
                            <!-- Formulario de Asignación Manual -->
                            <form action="{{ route('coordinator.manualAssign', $project) }}" method="POST" class="flex items-center space-x-2 w-full md:w-auto">
                                @csrf
                                <select name="tutor_id" class="text-sm rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500" required>
                                    <option value="">{{ __('Select Teacher') }}</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}">
                                            {{ $teacher->name }} ({{ __('Slots:') }} {{ $teacher->availableTutorSlots() }})
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="bg-upta-600 text-white px-3 py-1.5 rounded-md text-xs font-bold hover:bg-upta-700 transition shadow-sm whitespace-nowrap">
                                    {{ __('Assign') }}
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="p-10 text-center text-gray-500">
                            {{ __('All projects have an assigned tutor.') }}
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Panel de Docentes y Cupos -->
            <div class="bg-white shadow-md rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">{{ __('Teacher Capacities') }}</h3>
                <ul class="space-y-4">
                    @foreach($teachers as $teacher)
                        <li class="flex justify-between items-center text-sm border-b border-gray-100 pb-2">
                            <span class="text-gray-700 font-medium">{{ $teacher->name }}</span>
                            <span class="bg-upta-50 text-upta-700 font-bold px-2 py-1 rounded">
                                {{ $teacher->availableTutorSlots() }} / {{ $teacher->max_tutored_projects }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>