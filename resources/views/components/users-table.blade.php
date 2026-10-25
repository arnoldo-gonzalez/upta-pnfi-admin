@use('App\Enums\UserStatus')

@props([
    'users', 
    'roleName' => 'User', 
    'routePrefix', 
    'showApproval' => false
])

<div {{ $attributes->merge(['class' => 'overflow-x-auto']) }}>
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-upta-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase tracking-wider">
                    {{ __('ID Number') }}
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase tracking-wider">
                    {{ __($roleName) }}
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase tracking-wider">
                    {{ __('Contact') }}
                </th>
                <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-upta-900 uppercase tracking-wider">
                    {{ __('Status') }}
                </th>
                <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-upta-900 uppercase tracking-wider">
                    {{ __('Actions') }}
                </th>
            </tr>
        </thead>
        
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($users as $user)
                <tr class="hover:bg-gray-50 transition-colors">
                    
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $user->credential }}
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $user->phone }}
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        @if($user->status_id === UserStatus::PENDING->value)
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                {{ __('Pending') }}
                            </span>
                        @elseif($user->status_id === UserStatus::ACTIVE->value)
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                {{ __('Active') }}
                            </span>
                        @else
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                {{ __('Rejected') }}
                            </span>
                        @endif
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                        @if($showApproval && $user->status_id === UserStatus::PENDING->value)
                            <form action="{{ route($routePrefix . '.approve', $user->id) }}" method="POST" class="inline-block">
                                @csrf
                                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded-md text-xs font-bold transition-colors shadow-sm">
                                    {{ __('Approve') }}
                                </button>
                            </form>
                            <form action="{{ route($routePrefix . '.reject', $user->id) }}" method="POST" class="inline-block">
                                <button type="submit" class="bg-red-500 hover:bg-green-600 text-white px-3 py-1.5 rounded-md text-xs font-bold transition-colors shadow-sm">
                                    {{ __('Reject') }}
                                </button>
                            </form>
                        @elseif($user->status_id === UserStatus::ACTIVE->value)
                            <a href="#" class="text-upta-600 hover:text-upta-900 font-bold text-xs underline">
                                {{ __('View Details') }}
                            </a>
                        @else
                            <span class="text-gray-400 italic text-xs">{{ __('No actions') }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                        <p class="text-sm font-medium">{{ __('No records found for this role.') }}</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    @if($users->hasPages())
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            {{ $users->links() }}
        </div>
    @endif
</div>