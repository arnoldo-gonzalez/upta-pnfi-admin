@use('App\Enums\UserStatus')

<x-app-layout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        
        <!-- Encabezado de impresión -->
        <x-print-header title="Protocols Report" />

        <div class="flex justify-between items-center mb-6 no-print">
            <x-heading level="h1" class="mb-0">{{ __('Protocols Report') }}</x-heading>
            
            <button onclick="printReport('{{ route('reports.print', 'protocols') }}')" class="bg-gray-800 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-gray-900 transition shadow-sm flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                {{ __('Export to PDF') }}
            </button>
        </div>

        <div class="bg-white shadow-md rounded-lg border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-upta-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('Project') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('Section') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('Students') }}</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-upta-900 uppercase">{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($projects as $project)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $project->title }}</div>
                                <div class="text-xs text-gray-500">{{ $project->investigationLine->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $project->section->name }} - {{ $project->section->trayecto->name }} ({{ $project->section->period }})
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <ul class="list-disc list-inside">
                                    @foreach($project->students as $student)
                                        <li>{{ $student->enrollment->student->name }}</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($project->status_id === UserStatus::PENDING->value)
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">{{ __('In Review') }}</span>
                                @elseif($project->status_id === UserStatus::NEEDS_CORRECTIONS->value)
                                    <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">{{ __('Needs Corrections') }}</span>
                                @elseif($project->status_id === UserStatus::ACTIVE->value)
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">{{ __('Approved') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500">{{ __('No protocols found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if(!request()->has('export'))
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $projects->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>