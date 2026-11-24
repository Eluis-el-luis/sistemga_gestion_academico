@props(['active'])

@php
    $classes = ($active ?? false)
        ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-[#e6ac27] text-start text-base font-semibold text-neutral-900 dark:text-white bg-amber-50/70 dark:bg-neutral-800 focus:outline-none transition duration-150 ease-in-out cursor-pointer'
        : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-neutral-600 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white hover:bg-stone-100 dark:hover:bg-neutral-800/70 hover:border-stone-300 dark:hover:border-neutral-700 focus:outline-none transition duration-150 ease-in-out cursor-pointer';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>