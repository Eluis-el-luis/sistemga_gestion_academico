<a {{ $attributes->merge(['class' => 'block w-full px-4 py-2 text-start text-sm leading-5 font-medium text-neutral-700 dark:text-neutral-200 hover:bg-stone-100 dark:hover:bg-neutral-800 focus:outline-none focus:bg-stone-100 dark:focus:bg-neutral-800 transition duration-150 ease-in-out cursor-pointer']) }}>
    {{ $slot }}
</a>