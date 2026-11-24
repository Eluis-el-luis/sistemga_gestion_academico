<x-guest-layout>
    <div class="min-h-screen bg-stone-50 dark:bg-neutral-950 flex items-center justify-center p-4 transition-colors">
        <div class="w-full max-w-md bg-white dark:bg-neutral-900 border border-stone-200 dark:border-neutral-800 rounded-3xl p-6 sm:p-8 shadow-xs space-y-5">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-neutral-900 dark:text-white">{{ __('Register') }}</h2>
                <x-ui.theme-toggle />
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                <div>
                    <x-input-label for="name" :value="__('Name')" />
                    <x-text-input id="name" class="block mt-1.5 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" class="block mt-1.5 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input id="password" class="block mt-1.5 w-full" type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                    <x-text-input id="password_confirmation" class="block mt-1.5 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <div class="flex items-center justify-between pt-2">
                    <a class="underline text-sm text-neutral-600 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-[#e6ac27] cursor-pointer" href="{{ route('login') }}">
                        {{ __('Already registered?') }}
                    </a>

                    <x-primary-button class="ms-4">
                        {{ __('Register') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>