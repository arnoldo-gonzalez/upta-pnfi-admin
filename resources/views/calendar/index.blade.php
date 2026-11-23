<x-app-layout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8" x-data="{ openTermForm: {{ $errors->any() ? 'true' : 'false' }} }">
        
        @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                <ul class="list-disc pl-5 text-sm space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Encabezado y Botón de Nuevo Trimestre -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <x-heading level="h1" class="mb-0">{{ __('Academic Calendar') }}</x-heading>
                <p class="text-sm text-gray-500 mt-1">{{ __('Manage the academic terms and their key deadlines.') }}</p>
            </div>
            <button @click="openTermForm = !openTermForm" class="bg-upta-600 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-upta-700 transition shadow-sm flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                {{ __('New Term') }}
            </button>
        </div>

        <!-- Formulario para crear un Trimestre -->
        <div x-show="openTermForm" x-transition class="bg-white p-6 rounded-lg shadow-md border border-upta-200 mb-6" style="display: none;">
            <h3 class="text-lg font-bold text-gray-800 mb-4">{{ __('Create New Academic Term') }}</h3>
            <form action="{{ route('calendar.storeTerm') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Term Name') }}</label>
                        <input type="text" name="name" placeholder="Ej: Trimestre 1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Period') }}</label>
                        <input type="text" name="period" placeholder="Ej: 2025-2026" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Start Date') }}</label>
                        <input type="date" name="start_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('End Date') }}</label>
                        <input type="date" name="end_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500" required>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded-md font-semibold hover:bg-gray-900 transition shadow-sm">
                        {{ __('Save Term') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Listado de Trimestres -->
        <div class="space-y-6">
            @forelse($terms as $term)
                <div class="bg-white shadow-md rounded-lg border border-gray-200 overflow-hidden" x-data="{ openActivityForm: false }">
                    
                    <!-- Cabecera del Trimestre -->
                    <div class="bg-upta-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-bold text-upta-900">{{ $term->name }} <span class="text-sm font-normal text-gray-500">({{ $term->period }})</span></h3>
                            <p class="text-xs text-gray-600 mt-1">
                                {{ \Carbon\Carbon::parse($term->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($term->end_date)->format('d/m/Y') }}
                            </p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <button @click="openActivityForm = !openActivityForm" class="text-sm font-semibold text-upta-600 hover:text-upta-800 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                {{ __('Add Activity') }}
                            </button>
                            <x-confirm-delete :action="route('calendar.destroyTerm', $term)" title="Delete Term" message="Are you sure you want to delete this term and all its activities?" />
                        </div>
                    </div>

                    <!-- Formulario de Nueva Actividad (Oculto por defecto) -->
                    <div x-show="openActivityForm" x-transition class="bg-gray-50 px-6 py-4 border-b border-gray-200" style="display: none;">
                        <form action="{{ route('calendar.storeActivity', $term) }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-3">
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-gray-700">{{ __('Activity Name') }}</label>
                                    <input type="text" name="name" placeholder="Ej: Carga de Protocolos" class="mt-1 block w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500" required>
                                </div>
                                <div class="md:col-span-1">
                                    <label class="block text-xs font-bold text-gray-700">{{ __('Type') }}</label>
                                    <select name="type" class="mt-1 block w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500" required>
                                        <option value="protocol_submission">{{ __('Protocol Submission') }}</option>
                                        <option value="tutor_request">{{ __('Tutor Request') }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700">{{ __('Start') }}</label>
                                    <input type="date" name="start_date" class="mt-1 block w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700">{{ __('End') }}</label>
                                    <input type="date" name="end_date" class="mt-1 block w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500" required>
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="bg-upta-600 text-white px-4 py-1.5 rounded-md text-xs font-bold hover:bg-upta-700 transition">
                                    {{ __('Save Activity') }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Listado de Actividades del Trimestre -->
                    <div class="p-6">
                        <ul class="divide-y divide-gray-100">
                            @forelse($term->activities as $activity)
                                <li class="py-3 flex justify-between items-center">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $activity->name }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ \Carbon\Carbon::parse($activity->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($activity->end_date)->format('d/m/Y') }}
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-4">
                                        <!-- Etiqueta del tipo de actividad -->
                                        @if($activity->type === 'protocol_submission')
                                            <span class="px-2 py-1 bg-blue-100 text-blue-800 text-[10px] font-bold uppercase rounded">{{ __('Protocol') }}</span>
                                        @elseif($activity->type === 'tutor_request')
                                            <span class="px-2 py-1 bg-purple-100 text-purple-800 text-[10px] font-bold uppercase rounded">{{ __('Tutor') }}</span>
                                        @else
                                            <span class="px-2 py-1 bg-gray-100 text-gray-800 text-[10px] font-bold uppercase rounded">{{ $activity->type }}</span>
                                        @endif
                                        
                                        <!-- Botón eliminar actividad -->
                                        <form action="{{ route('calendar.destroyActivity', $activity) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ __('Are you sure?') }}');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-600 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </li>
                            @empty
                                <li class="py-4 text-sm text-gray-500 text-center italic">{{ __('No activities defined for this term yet.') }}</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            @empty
                <div class="bg-white p-10 rounded-lg shadow-sm border border-gray-200 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <p class="text-gray-500">{{ __('No academic terms created yet. Create one to start defining deadlines.') }}</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>