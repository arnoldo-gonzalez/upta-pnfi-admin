@use('App\Models\Trayecto')

<x-guest-layout>
    <!-- Contenedor con padding en móviles -->
    <div class="w-full px-4 sm:px-0">
        <x-heading class="text-white text-center" level="h2">{{ __("Register now!") }}</x-heading>
        
        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Name -->
            <div>
                <x-input-label for="name" :value="__('Name')" />
                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- Email Address -->
            <div class="mt-4">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Section -->
            <div class="mt-4">
                <x-input-label for="section_id" :value="__('Section')" />
                <select name="section_id" id="section_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                    <option value="">{{ __('Select your section') }}</option>
                    @foreach(Trayecto::with('sections')->get() as $trayecto)
                        <optgroup label="{{ $trayecto->name }}">
                            @foreach($trayecto->sections as $section)
                                <option value="{{ $section->id }}" {{ old('section_id') == $section->id ? 'selected' : '' }}>
                                    {{ $section->name }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('section_id')" class="mt-2" />
            </div>

            <!-- Credential & Phone (Se apilan en móvil, lado a lado en PC) -->
            <div class="flex flex-col sm:flex-row gap-4 mt-4">
                <!-- Credential -->
                <div class="w-full sm:w-1/2">
                    <x-input-label for="credential" :value="__('Credential')" />
                    <x-text-input id="credential" class="block mt-1 w-full" type="number" name="credential" :value="old('credential')" required />
                    <x-input-error :messages="$errors->get('credential')" class="mt-2" />
                </div>
        
                <!-- Phone number -->
                <div class="w-full sm:w-1/2">
                    <x-input-label for="phone" :value="__('Phone')" />
                    <x-text-input id="phone" class="block mt-1 w-full" type="number" name="phone" :value="old('phone')" required />
                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                </div>
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <!-- Botones finales -->
            <div class="flex flex-col-reverse sm:flex-row items-center justify-between mt-6 gap-4 sm:gap-0">
                <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>

                <x-primary-button class="w-full sm:w-auto justify-center py-3 sm:py-2">
                    {{ __('Register') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>