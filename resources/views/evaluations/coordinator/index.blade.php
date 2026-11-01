<x-app-layout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <x-heading level="h1">{{ __('Project Evaluations') }}</x-heading>

        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200 mt-6">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-upta-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('Project') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('Section') }}</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-upta-900 uppercase">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($projects as $project)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $project->title }}</div>
                                <div class="text-xs text-gray-500">{{ $project->investigationLine->name }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $project->section->name }} - {{ $project->section->trayecto->name }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('evaluations.coordinator.assign', $project) }}" class="bg-upta-600 text-white px-3 py-1.5 rounded-md text-xs font-bold hover:bg-upta-700 transition">
                                    {{ __('Assign Evaluators') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-6 py-10 text-center text-gray-500">{{ __('No pending projects to evaluate.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-gray-200">{{ $projects->links() }}</div>
        </div>
    </div>
</x-app-layout>