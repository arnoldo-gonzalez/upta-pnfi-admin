<x-app-layout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        
        <!-- Encabezado -->
        <div class="flex justify-between items-center mb-6">
            <x-heading level="h1">{{ __('Sections') }}</x-heading>
            
            <div class="flex flex-wrap gap-2 items-center">
                <!-- Botón para Cerrar Periodo -->
                <x-confirm-action 
                    :action="route('sections.closePeriod')" 
                    method="POST"
                    title="Close Active Period"
                    message="Are you sure you want to close ALL active sections? No new students will be able to enroll until you create new sections."
                    confirmText="Close Period"
                    icon="warning"
                >
                    <button type="button" class="bg-red-50 text-red-600 border border-red-200 px-4 py-2 rounded-md text-sm font-semibold hover:bg-red-100 transition">
                        {{ __('Close Active Period') }}
                    </button>
                </x-confirm-action>

                <!-- Botón para Clonar Periodo (Abre un pequeño formulario con Alpine) -->
                <div x-data="{ openClone: false }" class="relative">
                    <button @click="openClone = !openClone" class="bg-gray-100 text-gray-700 border border-gray-300 px-4 py-2 rounded-md text-sm font-semibold hover:bg-gray-200 transition">
                        {{ __('Clone Period') }}
                    </button>
                    
                    <div x-show="openClone" @click.away="openClone = false" class="absolute right-0 mt-2 w-64 bg-white p-4 rounded shadow-lg border border-gray-200 z-50" style="display: none;">
                        <form action="{{ route('sections.clonePeriod') }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <label class="text-xs font-bold text-gray-600">{{ __('Current Period') }}</label>
                                <input type="text" name="current_period" placeholder="2024-2025" class="w-full text-sm rounded border-gray-300" required>
                            </div>
                            <div class="mb-3">
                                <label class="text-xs font-bold text-gray-600">{{ __('New Period') }}</label>
                                <input type="text" name="new_period" placeholder="2025-2026" class="w-full text-sm rounded border-gray-300" required>
                            </div>
                            <button type="submit" class="w-full bg-upta-600 text-white text-xs font-bold py-2 rounded hover:bg-upta-700">
                                {{ __('Clone Now') }}
                            </button>
                        </form>
                    </div>
                </div>

                <a href="{{ route('sections.create') }}" class="bg-upta-600 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-upta-700 transition shadow-sm">
                    {{ __('Create Section') }}
                </a>
            </div>
        </div>

        <!-- Tabla -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-upta-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase tracking-wider">{{ __('Section') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase tracking-wider">{{ __('Trayecto') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase tracking-wider">{{ __('Period') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase tracking-wider">{{ __('Advisor (Teacher)') }}</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-upta-900 uppercase tracking-wider">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($sections as $section)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $section->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $section->trayecto->name }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $section->period }} 
                                @if(!$section->is_active)
                                    <span class="text-xs text-red-500 font-bold ml-1">({{ __('Closed') }})</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($section->activeTeacher->first())
                                    {{ $section->activeTeacher->first()->name }}
                                @else
                                    <span class="text-gray-400 italic text-xs">{{ __('No advisor assigned') }}</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <div class="flex items-center justify-center space-x-3">
                                    
                                    <!-- Botón Editar -->
                                    <a href="{{ route('sections.edit', $section) }}" class="text-upta-600 hover:text-upta-900 transition-colors" title="{{ __('Edit') }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>

                                    <!-- Botón Eliminar (Requiere Formulario) -->
                                    <x-confirm-delete 
                                        :action="route('sections.destroy', $section)" 
                                        title="Delete Section"
                                        message="Are you sure you want to delete this section? All related student enrollments will also be removed." 
                                    />

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                {{ __('No sections found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($sections->hasPages())
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    {{ $sections->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>