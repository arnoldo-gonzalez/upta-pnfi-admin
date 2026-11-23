<x-app-layout>
    <div class="max-w-5xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center space-x-4">
            <a href="{{ route('coordinator.tutorships') }}" class="text-gray-500 hover:text-upta-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <x-heading level="h1" class="mb-0">{{ __('Historial de tutorías') }}</x-heading>
                <p class="text-sm text-gray-500">{{ $project->title }}</p>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <p class="text-sm text-gray-600">
                    {{ __('Tutor:') }} <strong>{{ $project->tutor?->name ?? __('Sin tutor') }}</strong>
                    <span class="mx-2">·</span>
                    {{ __('Sección:') }} {{ $project->section->name }} - {{ $project->section->trayecto->name }}
                </p>
            </div>

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-upta-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('Fecha') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('Minuta') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('Estado') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('Revisión') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $log->meeting_date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ $log->description }}
                                @if($log->file_path)
                                    <br><a href="{{ route('tutor-logs.download', $log) }}" class="text-xs text-upta-600 hover:underline">{{ __('Descargar adjunto') }}</a>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($log->status_id === \App\Enums\UserStatus::ACTIVE->value)
                                    <span class="text-green-700">{{ __('Aprobada') }}</span>
                                @elseif($log->status_id === \App\Enums\UserStatus::REJECTED->value)
                                    <span class="text-red-700">{{ __('Rechazada') }}</span>
                                @else
                                    <span class="text-yellow-700">{{ __('Pendiente') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                @if($log->reviewed_at)
                                    <span class="text-xs text-gray-500">{{ $log->reviewer?->name }} · {{ $log->reviewed_at->format('d/m/Y') }}</span>
                                    @if($log->teacher_observations)
                                        <br><span class="text-xs italic">{{ $log->teacher_observations }}</span>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400">{{ __('Sin revisar') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500">{{ __('Este proyecto no tiene bitácoras registradas.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>

            @if($logs->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">{{ $logs->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
