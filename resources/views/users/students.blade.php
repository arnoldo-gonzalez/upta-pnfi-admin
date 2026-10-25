<x-app-layout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <x-heading level="h1">{{ __('Student Report') }}</x-heading>
            
            <button class="bg-upta-600 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-upta-700 transition">
                {{ __('Export PDF') }}
            </button>
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white shadow-md rounded-lg border border-gray-200 overflow-hidden">
            <!-- Pasamos variables en inglés: $students -->
            <x-users-table 
                :users="$students" 
                roleName="{{ __('Student') }}" 
                routePrefix="students" 
                :showApproval="true" 
            />
        </div>
    </div>
</x-app-layout>