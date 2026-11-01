<x-app-layout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <x-heading level="h1">{{ __('Student Report') }}</x-heading>
            
            <button class="bg-upta-600 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-upta-700 transition">
                {{ __('Export PDF') }}
            </button>
        </div>

        <div class="bg-white shadow-md rounded-lg border border-gray-200 overflow-hidden">
            <x-users-table 
                :users="$students" 
                roleName="{{ __('Student') }}" 
                routePrefix="students" 
                :showApproval="true" 
                :showAcademicInfo="true" 
            />
        </div>
    </div>
</x-app-layout>