<x-app-layout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <x-heading level="h1">{{ __('My Assigned Evaluations') }}</x-heading>

        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200 mt-6">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-upta-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('Project') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('Stage') }}</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-upta-900 uppercase">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($evaluations as $evaluation)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $evaluation->project->title }}</div>
                                <div class="text-xs text-gray-500">{{ $evaluation->project->section->name }} - {{ $evaluation->project->section->trayecto->name }}</div>
                                <div class="text-xs text-gray-500">
                                    Envío #{{ $evaluation->document->submission_number ?? 1 }} - 
                                    {{ $evaluation->project->section->name }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $evaluation->stage->name }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('evaluations.teacher.review', $evaluation) }}" class="bg-blue-600 text-white px-3 py-1.5 rounded-md text-xs font-bold hover:bg-blue-700 transition">
                                    {{ __('Review') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-6 py-10 text-center text-gray-500">{{ __('No pending evaluations assigned to you.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-gray-200">{{ $evaluations->links() }}</div>
        </div>
    </div>
</x-app-layout>