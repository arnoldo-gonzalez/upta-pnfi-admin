<x-app-layout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <x-heading level="h1" class="mb-6">{{ __('Audit Logs') }}</x-heading>

        <div class="bg-white shadow-md rounded-lg border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-upta-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('Date') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('User') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('Action') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('Description') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('IP Address') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $log->created_at->format('d/m/Y h:i A') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $log->user ? $log->user->name : __('System') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-bold font-mono">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $log->description }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                                {{ $log->ip_address }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                {{ __('No audit logs recorded yet.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-gray-200">{{ $logs->links() }}</div>
        </div>
    </div>
</x-app-layout>