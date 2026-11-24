<x-guest-layout>
    <div class="min-h-screen bg-stone-50 dark:bg-neutral-950 flex items-center justify-center p-4 transition-colors">
        <div class="w-full max-w-md bg-white dark:bg-neutral-900 border border-stone-200 dark:border-neutral-800 rounded-3xl p-6 sm:p-8 shadow-xs space-y-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-[#b88212] dark:text-[#e6ac27]">Seguridad</span>
                <x-ui.theme-toggle />
            </div>

            <div class="text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed">
                {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
            </div>

            <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
                @csrf

                <div>
                    <x-input-label for="password" :value="__('Password')" />

                    <x-text-input
                        id="password"
                        class="block mt-1.5 w-full"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                    />

                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="flex justify-end pt-2">
                    <x-primary-button>
                        {{ __('Confirm') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>