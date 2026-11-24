<section>
    <header>
        <h2 class="text-base font-bold text-neutral-900 dark:text-white">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-5 space-y-5">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="nombre_completo" :value="__('Nombre Completo')" />
            <x-text-input id="nombre_completo" name="nombre_completo" type="text" class="mt-1.5 block w-full" :value="old('nombre_completo', $user->nombre_completo)" required autofocus autocomplete="name" />
            <x-input-error class="mt-1.5" :messages="$errors->get('nombre_completo')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1.5 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-1.5" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-xs text-neutral-700 dark:text-neutral-300">
                        {{ __('Your email address is unverified.') }}
                        <button form="send-verification" class="underline text-xs text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white cursor-pointer">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-xs font-semibold text-emerald-600 dark:text-emerald-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>