<x-guest-layout>
    <div class="w-full px-4 sm:px-0">
        <x-heading class="text-white text-center" level="h2">{{ __('Recuperar contraseña') }}</x-heading>

        <p class="mt-2 text-sm text-gray-600 text-center">
            {{ __('Ingresa tu correo para consultar tu pregunta de seguridad.') }}
        </p>

        <form method="POST" action="{{ route('password.security.find') }}" class="mt-4">
            @csrf

            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between mt-6">
                <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
                    {{ __('Volver al inicio de sesión') }}
                </a>

                <x-primary-button>{{ __('Continuar') }}</x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
