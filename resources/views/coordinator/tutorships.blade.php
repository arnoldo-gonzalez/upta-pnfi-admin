<x-app-layout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        
        <!-- Encabezado de impresión -->
        <x-print-header title="Tutorships Report" />

        <div class="flex justify-between items-center mb-6">
            <x-heading level="h1" class="mb-0">{{ __('Tutorships Report') }}</x-heading>
            
            <div class="flex space-x-3">
                <!-- Botón de Asignación Automática -->
                <a href="{{ route('coordinator.assignTutors') }}" class="bg-upta-600 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-upta-700 transition shadow-sm flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    {{ __('Assign Tutors') }}
                </a>
                
                <x-confirm-action 
                    :action="route('coordinator.calculateQuota')" 
                    method="POST"
                    title="Calculate Fair Quota"
                    message="This will calculate (Total Projects / Total Teachers) and update every teacher's maximum limit. Continue?"
                    confirmText="Calculate"
                    icon="check"
                    buttonClass="bg-gray-600 hover:bg-gray-500"
                >
                    <button type="button" class="bg-gray-800 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-gray-900 transition shadow-sm flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        {{ __('Calculate Fair Quota') }}
                    </button>
                </x-confirm-action>
            
                <button onclick="printReport('{{ route('reports.print', 'tutorships') }}')" class="bg-gray-800 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-gray-900 transition shadow-sm flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    {{ __('Export to PDF') }}
                </button>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-upta-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('Project') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('Section') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('Tutor Assigned') }}</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-upta-900 uppercase">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($projects as $project)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <strong class="text-black">{{ $project->title }}</strong><br>
                                <span style="font-size: 10px; color: #666;">
                                    @if($project->code)
                                        {{ $project->code }}
                                    @else
                                        {{ $project->investigationLine->name ?? 'Sin línea' }}
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $project->section->name }} - {{ $project->section->trayecto->name }} ({{ $project->section->period }})
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                {{ $project->tutor?->name ?? __('No tutor') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('coordinator.tutor-logs', $project) }}" class="text-upta-600 hover:text-upta-800 text-xs font-semibold">
                                    {{ __('Ver bitácoras') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500">{{ __('No projects with assigned tutors found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-gray-200">{{ $projects->links() }}</div>
        </div>
    </div>
</x-app-layout>