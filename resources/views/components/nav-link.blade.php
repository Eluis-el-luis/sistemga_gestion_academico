@props(['active'])

@php
    $classes = ($active ?? false)
        ? 'inline-flex items-center px-1 pt-1 border-b-2 border-[#e6ac27] text-sm font-semibold leading-5 text-neutral-900 dark:text-white focus:outline-none focus:border-[#cf9719] transition duration-150 ease-in-out cursor-pointer'
        : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-neutral-500 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100 hover:border-stone-300 dark:hover:border-neutral-700 focus:outline-none focus:text-neutral-900 dark:focus:text-neutral-100 focus:border-stone-300 transition duration-150 ease-in-out cursor-pointer';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>