<button
    type="button"
    x-data="{ isDark: document.documentElement.classList.contains('dark') }"
    @theme-changed.window="isDark = $event.detail.dark"
    @click="isDark = window.toggleTheme ? window.toggleTheme() : document.documentElement.classList.toggle('dark')"
    :aria-pressed="isDark.toString()"
    :title="isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border border-stone-200 dark:border-neutral-700 bg-stone-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-200 hover:border-[#e6ac27] text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-[#e6ac27] cursor-pointer"
>
    <svg x-show="!isDark" class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
    </svg>
    <svg x-show="isDark" x-cloak class="w-4 h-4 text-[#e6ac27] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
    </svg>
    <span x-text="isDark ? 'Claro' : 'Oscuro'" class="hidden sm:inline"></span>
</button>