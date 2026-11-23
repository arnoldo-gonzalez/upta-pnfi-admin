<x-guest-layout>
    <div class="w-full px-4 sm:px-0">
        <x-heading class="text-white text-center" level="h2">{{ __('Pregunta de seguridad') }}</x-heading>

        <p class="mt-2 text-sm text-gray-600 text-center">{{ $question }}</p>

        <form method="POST" action="{{ route('password.security.reset') }}" class="mt-4">
            @csrf

            <input type="hidden" name="email" value="{{ $email }}">

            <div>
                <x-input-label for="security_answer" :value="__('Respuesta')" />
                <x-text-input id="security_answer" class="block mt-1 w-full" type="text" name="security_answer" required autofocus />
                <x-input-error :messages="$errors->get('security_answer')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="password" :value="__('Nueva contraseña')" />
                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="password_confirmation" :value="__('Confirmar contraseña')" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>

            <div class="flex items-center justify-between mt-6">
                <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('password.security.request') }}">
                    {{ __('Usar otro correo') }}
                </a>

                <x-primary-button>{{ __('Restablecer') }}</x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
