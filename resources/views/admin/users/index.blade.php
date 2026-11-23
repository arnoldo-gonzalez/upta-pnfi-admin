<x-app-layout>
    <div class="max-w-6xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <x-heading level="h1" class="mb-0">{{ __('Usuarios del sistema') }}</x-heading>
            <a href="{{ route('admin.users.create') }}" class="bg-upta-600 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-upta-700 transition shadow-sm">
                {{ __('Crear usuario') }}
            </a>
        </div>

        <div class="bg-white shadow-md rounded-lg border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-upta-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('Nombre') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('Correo') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('Cédula') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('Rol') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-upta-900 uppercase">{{ __('Estado') }}</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-upta-900 uppercase">{{ __('Acciones') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $user->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $user->email }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $user->credential }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700 capitalize">{{ $user->roles->pluck('name')->join(', ') }}</td>
                            <td class="px-6 py-4 text-sm">
                                @if($user->status_id === \App\Enums\UserStatus::ACTIVE->value)
                                    <span class="text-green-700">{{ __('Activo') }}</span>
                                @elseif($user->status_id === \App\Enums\UserStatus::PENDING->value)
                                    <span class="text-yellow-700">{{ __('Pendiente') }}</span>
                                @else
                                    <span class="text-red-700">{{ __('Inactivo') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-upta-600 hover:text-upta-800 text-xs font-semibold">{{ __('Editar') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">{{ __('No hay usuarios registrados.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-gray-200">{{ $users->links() }}</div>
        </div>
    </div>
</x-app-layout>
