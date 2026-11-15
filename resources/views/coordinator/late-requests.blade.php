<x-app-layout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <x-heading level="h1" class="mb-6">{{ __('Late Submission Requests') }}</x-heading>

        <div class="bg-white shadow-md rounded-lg border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-upta-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('Project') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('Student') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('Reason') }}</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-upta-900 uppercase">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($requests as $req)
                        <!-- Envolvemos la fila en Alpine para manejar su propio modal -->
                        <tr x-data="{ showReasonModal: false }">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $req->project->title }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $req->user->name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 max-w-[250px]">
                                <!-- Truncamos el texto para evitar overflow -->
                                <p class="italic truncate" title="{{ $req->reason }}">
                                    "{{ Str::limit($req->reason, 50) }}"
                                </p>
                                <!-- Botón para abrir el modal -->
                                <button @click="showReasonModal = true" class="text-xs text-upta-600 hover:text-upta-800 font-semibold mt-1 underline">
                                    {{ __('Read full reason') }}
                                </button>

                                <!-- MODAL DE LA JUSTIFICACIÓN (Alpine) -->
                                <div x-show="showReasonModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                    <!-- Fondo oscuro -->
                                    <div x-show="showReasonModal" 
                                        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-90" 
                                        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-90" x-transition:leave-end="opacity-0" 
                                        class="fixed inset-0 bg-gray-900 opacity-90 transition-opacity" 
                                        @click="showReasonModal = false"></div>

                                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                                        <!-- Panel del Modal -->
                                        <div x-show="showReasonModal" 
                                             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                                             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                                             class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                                            
                                            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                                <h3 class="text-lg font-semibold leading-6 text-gray-900 mb-4" id="modal-title">
                                                    {{ __('Late Submission Reason') }}
                                                </h3>
                                                <div class="bg-gray-50 p-4 rounded border border-gray-200 text-sm text-gray-700 whitespace-pre-wrap max-h-60 overflow-y-auto">
                                                    "{{ $req->reason }}"
                                                </div>
                                            </div>
                                            
                                            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                                <!-- Botón Rechazar -->
                                                <form action="{{ route('late-requests.review', $req) }}" method="POST" class="inline-block w-full sm:w-auto mt-2 sm:mt-0 sm:ml-3">
                                                    @csrf
                                                    <input type="hidden" name="decision" value="rejected">
                                                    <button type="submit" class="w-full inline-flex justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 transition-colors">
                                                        {{ __('Reject') }}
                                                    </button>
                                                </form>
                                                <!-- Botón Aprobar -->
                                                <form action="{{ route('late-requests.review', $req) }}" method="POST" class="inline-block w-full sm:w-auto">
                                                    @csrf
                                                    <input type="hidden" name="decision" value="approved">
                                                    <button type="submit" class="w-full inline-flex justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 transition-colors">
                                                        {{ __('Approve') }}
                                                    </button>
                                                </form>
                                                <!-- Botón Cerrar (móvil) -->
                                                <button type="button" @click="showReasonModal = false" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:hidden">
                                                    {{ __('Cancel') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <!-- Botones rápidos en la tabla -->
                                <div class="flex items-center justify-center space-x-2">
                                    <form action="{{ route('late-requests.review', $req) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="decision" value="approved">
                                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded-md text-xs font-bold transition-colors">
                                            {{ __('Approve') }}
                                        </button>
                                    </form>
                                    <form action="{{ route('late-requests.review', $req) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="decision" value="rejected">
                                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-md text-xs font-bold transition-colors">
                                            {{ __('Reject') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                {{ __('No pending late submission requests.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-gray-200">{{ $requests->links() }}</div>
        </div>
    </div>
</x-app-layout>