@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-amber-300 text-sm font-medium leading-5 text-slate-900 focus:outline-none focus:border-[rgb(var(--club-brand))] transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-slate-500 hover:text-slate-700 hover:border-amber-200 focus:outline-none focus:text-slate-700 focus:border-amber-200 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
