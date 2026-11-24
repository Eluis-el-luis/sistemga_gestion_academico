<x-guest-layout>
    <div class="min-h-screen bg-stone-50 dark:bg-neutral-950 flex items-center justify-center p-4 transition-colors">
        <div class="w-full max-w-md bg-white dark:bg-neutral-900 border border-stone-200 dark:border-neutral-800 rounded-3xl p-6 sm:p-8 shadow-xs space-y-5">
            <div class="flex justify-end">
                <x-ui.theme-toggle />
            </div>

            <div class="text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed">
                {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="font-medium text-sm text-emerald-700 dark:text-emerald-400">
                    {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                </div>
            @endif

            <div class="pt-2 flex items-center justify-between gap-4">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <x-primary-button>
                        {{ __('Resend Verification Email') }}
                    </x-primary-button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="underline text-sm font-medium text-neutral-600 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-[#e6ac27] cursor-pointer">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>