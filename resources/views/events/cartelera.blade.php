<x-app-layout>
    <div class="max-w-5xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="mb-6">
            <x-heading level="h1" class="mb-0">{{ __('Cartelera de socializaciones y eventos') }}</x-heading>
            <p class="text-sm text-gray-500 mt-1">{{ __('Próximos eventos académicos visibles para tu rol.') }}</p>
        </div>

        <div class="space-y-4">
            @forelse($events as $event)
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-5">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                        <div>
                            <h3 class="text-lg font-bold text-upta-900">{{ $event->title }}</h3>
                            <p class="text-sm text-gray-600">
                                {{ $event->start_date->format('d/m/Y h:i A') }}
                                @if($event->location)
                                    · {{ $event->location }}
                                @endif
                            </p>
                            @if($event->project)
                                <p class="text-sm text-upta-600 mt-1">{{ $event->project->title }}</p>
                            @endif
                            @if($event->description)
                                <p class="text-sm text-gray-500 mt-2">{{ $event->description }}</p>
                            @endif
                        </div>
                        <span class="text-xs font-semibold uppercase tracking-wide text-upta-700 bg-upta-50 px-3 py-1 rounded-full">
                            {{ $event->type->name ?? __('Evento') }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-8 text-center text-gray-500">
                    {{ __('No hay eventos próximos para tu rol.') }}
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
