<x-app-layout>
    <div class="max-w-3xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center space-x-4">
            <a href="{{ route('admin.users.index') }}" class="text-gray-500 hover:text-upta-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <x-heading level="h1" class="mb-0">{{ __('Crear usuario') }}</x-heading>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6 border border-gray-200">
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf

                @if($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                        <ul class="list-disc pl-5 text-sm space-y-1">
                            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Nombre') }}</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="mt-1 block w-full rounded-md border-gray-300" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Correo') }}</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="mt-1 block w-full rounded-md border-gray-300" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Cédula') }}</label>
                        <input type="number" name="credential" value="{{ old('credential') }}" class="mt-1 block w-full rounded-md border-gray-300" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Teléfono') }}</label>
                        <input type="number" name="phone" value="{{ old('phone') }}" class="mt-1 block w-full rounded-md border-gray-300" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Rol') }}</label>
                        <select name="role" class="mt-1 block w-full rounded-md border-gray-300" required>
                            @foreach($roles as $role)
                                <option value="{{ $role }}" {{ old('role') === $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Estado') }}</label>
                        <select name="status_id" class="mt-1 block w-full rounded-md border-gray-300" required>
                            <option value="{{ \App\Enums\UserStatus::ACTIVE->value }}" {{ old('status_id') == \App\Enums\UserStatus::ACTIVE->value ? 'selected' : '' }}>{{ __('Activo') }}</option>
                            <option value="{{ \App\Enums\UserStatus::PENDING->value }}" {{ old('status_id') == \App\Enums\UserStatus::PENDING->value ? 'selected' : '' }}>{{ __('Pendiente') }}</option>
                            <option value="{{ \App\Enums\UserStatus::REJECTED->value }}" {{ old('status_id') == \App\Enums\UserStatus::REJECTED->value ? 'selected' : '' }}>{{ __('Inactivo') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Contraseña') }}</label>
                        <input type="password" name="password" class="mt-1 block w-full rounded-md border-gray-300" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Máximo de tutorías (docente)') }}</label>
                        <input type="number" name="max_tutored_projects" value="{{ old('max_tutored_projects', 5) }}" min="1" class="mt-1 block w-full rounded-md border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Especialidad (docente)') }}</label>
                        <input type="text" name="specialty" value="{{ old('specialty') }}" class="mt-1 block w-full rounded-md border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Disponibilidad horaria (docente)') }}</label>
                        <input type="text" name="availability" value="{{ old('availability') }}" class="mt-1 block w-full rounded-md border-gray-300">
                    </div>
                </div>

                <div class="mb-6">
                    <input type="hidden" name="jury_available" value="0">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="jury_available" value="1" class="rounded border-gray-300 text-upta-600 shadow-sm focus:ring-upta-500" {{ old('jury_available', true) ? 'checked' : '' }}>
                        <span class="ml-2 text-sm text-gray-700">{{ __('Disponible para actuar como jurado') }}</span>
                    </label>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700">{{ __('Sección (solo estudiantes)') }}</label>
                    <select name="section_id" class="mt-1 block w-full rounded-md border-gray-300">
                        <option value="">{{ __('Selecciona una sección') }}</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}" {{ old('section_id') == $section->id ? 'selected' : '' }}>
                                {{ $section->name }} - {{ $section->trayecto->name }} ({{ $section->period }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-upta-600 text-white px-6 py-2 rounded-md font-semibold hover:bg-upta-700">{{ __('Crear usuario') }}</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
