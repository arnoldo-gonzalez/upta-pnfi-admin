<x-app-layout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <x-heading level="h1" class="mb-0">{{ __('Evaluation History') }}</x-heading>
            <a href="{{ route('evaluations.teacher.index') }}" class="text-sm font-semibold text-upta-600 hover:text-upta-800">
                &larr; {{ __('Back to Pending') }}
            </a>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
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
                                <a href="{{ route('evaluations.teacher.history.show', $project) }}" class="bg-gray-100 text-gray-700 border border-gray-300 px-3 py-1.5 rounded-md text-xs font-bold hover:bg-gray-200 transition">
                                    {{ __('View Timeline') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-6 py-10 text-center text-gray-500">{{ __('You have not evaluated any projects yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-gray-200">{{ $projects->links() }}</div>
        </div>
    </div>
</x-app-layout>