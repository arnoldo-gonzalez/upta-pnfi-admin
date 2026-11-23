<x-app-layout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <x-heading level="h1" class="mb-0">{{ __('Academic Events') }}</x-heading>
            <a href="{{ route('events.create') }}" class="bg-upta-600 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-upta-700 transition shadow-sm">
                {{ __('Create Event') }}
            </a>
        </div>

        <div class="bg-white shadow-md rounded-lg border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-upta-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('Event') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('Date') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('Visible To') }}</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-upta-900 uppercase">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($events as $event)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $event->title }}</div>
                                <div class="text-xs text-gray-500">{{ $event->type->name ?? 'General' }}</div>
                                @if($event->description)
                                    <div class="text-xs text-gray-600 mt-1">{{ $event->description }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($event->start_date)->format('d/m/Y h:i A') }}
                                @if($event->location)
                                    <div class="text-xs text-gray-400">{{ $event->location }}</div>
                                @endif
                                @if($event->project)
                                    <div class="text-xs text-upta-600">{{ $event->project->title }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($event->visible_to_roles ?? [] as $role)
                                        <span class="px-2 py-1 bg-gray-100 rounded text-xs capitalize">{{ $role }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('events.edit', $event) }}" class="text-upta-600 hover:text-upta-800 text-xs font-semibold">
                                        {{ __('Edit') }}
                                    </a>
                                    <x-confirm-delete :action="route('events.destroy', $event)" title="Delete Event" message="Are you sure you want to delete this event?" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500">{{ __('No events found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-gray-200">{{ $events->links() }}</div>
        </div>
    </div>
</x-app-layout>