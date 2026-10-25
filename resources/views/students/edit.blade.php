@use('App\Enums\UserStatus')

<x-app-layout>
    <div class="max-w-3xl mx-auto py-6 sm:px-6 lg:px-8">
        
        <div class="mb-6 flex items-center space-x-4">
            <a href="{{ route('students.index') }}" class="text-gray-500 hover:text-upta-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <x-heading level="h1" class="mb-0">{{ __('Edit Student') }}</x-heading>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6 border border-gray-200">
            <form action="{{ route('students.update', $student) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <!-- Nombre -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Full Name') }}</label>
                        <input type="text" name="name" value="{{ old('name', $student->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500" required>
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <!-- Correo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Email') }}</label>
                        <input type="email" name="email" value="{{ old('email', $student->email) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500" required>
                        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <!-- Cédula -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('ID Number') }}</label>
                        <input type="number" name="credential" value="{{ old('credential', $student->credential) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500" required>
                        @error('credential') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <!-- Teléfono -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Phone') }}</label>
                        <input type="number" name="phone" value="{{ old('phone', $student->phone) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500" required>
                        @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Estado -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
                    <select name="status_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500" required>
                        <option value="{{ UserStatus::PENDING->value }}" {{ old('status_id', $student->status_id) == UserStatus::PENDING->value ? 'selected' : '' }}>{{ __('Pending') }}</option>
                        <option value="{{ UserStatus::ACTIVE->value }}" {{ old('status_id', $student->status_id) == UserStatus::ACTIVE->value ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="{{ UserStatus::REJECTED->value }}" {{ old('status_id', $student->status_id) == UserStatus::REJECTED->value ? 'selected' : '' }}>{{ __('Rejected') }}</option>
                    </select>
                    @error('status_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Contraseña -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700">{{ __('New Password (Optional)') }}</label>
                    <input type="password" name="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-upta-500 focus:ring-upta-500">
                    @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    <p class="text-xs text-gray-500 mt-1">{{ __('Leave blank if you do not want to change the current password.') }}</p>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-upta-600 text-white px-6 py-2 rounded-md font-semibold hover:bg-upta-700 transition shadow-sm">
                        {{ __('Update Student') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>