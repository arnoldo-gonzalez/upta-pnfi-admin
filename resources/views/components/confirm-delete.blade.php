@props([
    'action', 
    'title' => 'Are you sure?', 
    'message' => 'This action cannot be undone.'
])

<div x-data="{ confirming: false }" class="inline-block">
    <!-- Botón que abre el modal (Icono de basura) -->
    <button type="button" @click="confirming = true" class="text-red-500 hover:text-red-700 transition-colors cursor-pointer" title="{{ __('Delete') }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
    </button>

    <!-- Modal de Confirmación -->
    <div x-show="confirming" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <!-- Fondo oscuro -->
        <div x-show="confirming" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-90" 
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-90" x-transition:leave-end="opacity-0" 
             class="fixed inset-0 bg-gray-900 opacity-90 transition-opacity" 
             @click="confirming = false"></div>

        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            
            <!-- Panel del Modal -->
            <div x-show="confirming" 
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 w-full sm:max-w-lg">
                
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        
                        <!-- Icono de advertencia -->
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        
                        <!-- Contenedor del texto CORREGIDO -->
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left flex-1 min-w-0 w-full">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900" id="modal-title">
                                {{ __($title) }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 break-words whitespace-normal">
                                    {{ __($message) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Botones de acción -->
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <form action="{{ $action }}" method="POST" class="inline-block w-full sm:w-auto">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto transition-colors">
                            {{ __('Delete') }}
                        </button>
                    </form>
                    
                    <button type="button" @click="confirming = false" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors">
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>