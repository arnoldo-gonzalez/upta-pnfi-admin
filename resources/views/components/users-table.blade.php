@use('App\Enums\UserStatus')

@props([
    'users', 
    'roleName' => 'User', 
    'routePrefix', 
    'showApproval' => false,
    'showAcademicInfo' => false
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
                @if($showAcademicInfo)
                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase tracking-wider">
                    {{ __('Academic Info') }}
                </th>
                @endif
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

                    @if($showAcademicInfo)
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        @php
                            // Tomamos la primera inscripción
                            $enrollment = $user->enrollments->first();
                        @endphp
                        
                        @if($enrollment && $enrollment->section)
                            <div class="text-sm font-medium text-gray-900">
                                {{ $enrollment->section->trayecto->name ?? __('No Trayecto') }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $enrollment->section->name }} 
                                <span class="text-gray-400">|</span> 
                                {{ $enrollment->section->period }}
                            </div>
                        @else
                            <span class="text-gray-400 italic text-xs">{{ __('No active enrollment') }}</span>
                        @endif
                    </td>
                    @endif

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

                    <!-- Botones de Acción Dinámicos -->
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                        @if($showApproval && $user->status_id === UserStatus::PENDING->value)
                            <!-- Botón de Aprobar (Solo para estudiantes pendientes) -->
                            <form action="{{ route($routePrefix . '.approve', $user->id) }}" method="POST" class="inline-block">
                                @csrf
                                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded-md text-xs font-bold transition-colors shadow-sm">
                                    {{ __('Approve') }}
                                </button>
                            </form>
                        @else
                            <!-- Botones de Editar y Eliminar -->
                            <div class="flex items-center justify-center space-x-3">
                                <!-- Editar -->
                                <a href="{{ route($routePrefix . '.edit', $user->id) }}" class="text-upta-600 hover:text-upta-900 transition-colors" title="{{ __('Edit') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>

                                <x-confirm-delete 
                                    :action="route($routePrefix . '.destroy', $user->id)" 
                                    title="Delete Teacher"
                                    message="Are you sure you want to delete this teacher? This action cannot be undone." 
                                />
                            </div>
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