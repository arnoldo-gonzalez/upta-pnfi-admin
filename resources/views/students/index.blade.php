<x-app-layout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <x-heading level="h1">{{ __('Students') }}</x-heading>
            
            <button onclick="printReport('{{ route('reports.print', 'students') }}')" class="bg-gray-800 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-gray-900 transition shadow-sm flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                {{ __('Export to PDF') }}
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