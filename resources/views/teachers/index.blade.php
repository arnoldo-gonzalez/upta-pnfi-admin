<x-app-layout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        
        <!-- Encabezado de la página -->
        <div class="flex justify-between items-center mb-6">
            <x-heading level="h1" class="mb-0">{{ __('Teacher Report') }}</x-heading>
            
            <!-- Botón para crear un nuevo docente -->
            <a href="{{ route('teachers.create') }}" class="bg-upta-600 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-upta-700 transition shadow-sm">
                {{ __('Create Teacher') }}
            </a>
        </div>

        <!-- Tabla Reutilizable -->
        <div class="bg-white shadow-md rounded-lg border border-gray-200 overflow-hidden">
            <x-users-table 
                :users="$teachers" 
                roleName="Teacher" 
                routePrefix="teachers" 
                :showApproval="false" 
            />
        </div>
    </div>
</x-app-layout>