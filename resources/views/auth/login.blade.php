<x-guest-layout>
    <!-- Contenedor con padding en móviles -->
    <div class="w-full px-4 sm:px-0">
        <x-heading class="text-white text-center" level="h2">{{ __("Welcome back!") }}</x-heading>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Remember Me -->
            <div class="block mt-4">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="remember">
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember Me') }}</span>
                </label>
            </div>

            <!-- Botones de Login y Recuperación (Apilados en móvil) -->
            <div class="flex flex-col-reverse sm:flex-row items-center justify-between mt-6 gap-4 sm:gap-0">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('password.request') }}">
                        {{ __('Forgot Your Password?') }}
                    </a>
                @endif

                <!-- Botón w-full en móvil -->
                <x-primary-button class="w-full sm:w-auto justify-center sm:ms-3 py-3 sm:py-2">
                    {{ __('Log in') }}
                </x-primary-button>
            </div>

            <!-- Registro de Estudiantes -->
            <div class="mt-8 text-center border-t border-gray-200 dark:border-gray-700 pt-6">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                    {{ __('Are you a student and don\'t have an account?') }}
                </p>

                <!-- Botón w-full en móvil -->
                <a href="{{ route('register') }}" class="inline-flex w-full sm:w-auto items-center justify-center px-4 py-3 sm:py-2 text-sm font-semibold text-blue-600 border border-blue-600 rounded-md hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                    {{ __('Register to await approval') }}
                </a>
            
                <p class="mt-4 text-xs text-gray-500 text-center max-w-xs mx-auto">
                    {{ __('* Your account will require approval from the advisor before you can access the system.') }}
                </p>
            </div>
        </form>
    </div>
</x-guest-layout>