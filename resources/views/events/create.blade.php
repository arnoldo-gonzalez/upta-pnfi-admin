<x-app-layout>
    <div class="max-w-3xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center space-x-4">
            <a href="{{ route('events.index') }}" class="text-gray-500 hover:text-upta-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <x-heading level="h1" class="mb-0">{{ __('Create Academic Event') }}</x-heading>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6 border border-gray-200">
            <form action="{{ route('events.store') }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Event Title') }}</label>
                        <input type="text" name="title" value="{{ old('title') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Event Type') }}</label>
                        <select name="event_type_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500" required>
                            @foreach($eventTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">{{ __('Date & Time') }}</label>
                    <input type="datetime-local" name="start_date" value="{{ old('start_date') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">{{ __('Description (Optional)') }}</label>
                    <textarea name="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500">{{ old('description') }}</textarea>
                </div>

                <!-- VISIBILIDAD Y ENLACES (Alpine.js) -->
                <div class="mb-6 border-t border-gray-100 pt-4" x-data="{ selectedRoles: [] }">
                    <label class="block text-sm font-bold text-gray-800 mb-2">{{ __('Visibility & Links') }}</label>
                    <p class="text-xs text-gray-500 mb-3">{{ __('Select who can see this event. You can optionally link them to a specific module.') }}</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($roles as $role)
                            <div class="bg-gray-50 p-3 rounded border border-gray-200">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="visible_to_roles[]" value="{{ $role }}" 
                                           x-model="selectedRoles" class="rounded border-gray-300 text-upta-600 shadow-sm focus:ring-upta-500">
                                    <span class="ml-2 text-sm font-medium text-gray-700 capitalize">{{ __($role) }}</span>
                                </label>

                                <!-- Select para el enlace, solo aparece si el rol está seleccionado -->
                                <div x-show="selectedRoles.includes('{{ $role }}')" class="mt-2" style="display: none;">
                                    <select name="role_links[{{ $role }}]" class="w-full text-xs rounded border-gray-300 focus:border-upta-500 focus:ring-upta-500">
                                        <option value="">{{ __('No link (Just informational)') }}</option>
                                        @foreach($availableRoutes[$role] as $routeName => $routeLabel)
                                            <option value="{{ $routeName }}">{{ __($routeLabel) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-upta-600 text-white px-6 py-2 rounded-md font-semibold hover:bg-upta-700 transition shadow-sm">
                        {{ __('Save Event') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>