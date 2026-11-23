<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="current_password" :value="__('Current Password (only to change your email)')" />
            <x-text-input id="current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
            <x-input-error class="mt-2" :messages="$errors->get('current_password')" />
        </div>

        <div class="border-t border-gray-200 pt-4">
            <h3 class="text-sm font-semibold text-gray-800">{{ __('Pregunta de seguridad') }}</h3>
            <p class="text-xs text-gray-500 mt-1">{{ __('Se usa para recuperar tu contraseña sin depender del correo.') }}</p>

            <div class="mt-3">
                <x-input-label for="security_question" :value="__('Pregunta')" />
                <x-text-input id="security_question" name="security_question" type="text" class="mt-1 block w-full"
                    :value="old('security_question', $user->security_question)"
                    placeholder="{{ __('Ej: ¿Cuál es el nombre de tu primera mascota?') }}" />
                <x-input-error class="mt-2" :messages="$errors->get('security_question')" />
            </div>

            <div class="mt-3">
                <x-input-label for="security_answer" :value="__('Respuesta')" />
                <x-text-input id="security_answer" name="security_answer" type="password" class="mt-1 block w-full" autocomplete="off" />
                <x-input-error class="mt-2" :messages="$errors->get('security_answer')" />
                @if ($user->security_answer)
                    <p class="text-xs text-green-600 mt-1">{{ __('Ya tienes una respuesta configurada. Déjala en blanco para conservarla.') }}</p>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
