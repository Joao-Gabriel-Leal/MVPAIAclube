@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-amber-300 text-start text-base font-medium text-[rgb(var(--club-brand))] bg-amber-50/70 focus:outline-none focus:text-[rgb(var(--club-brand))] focus:bg-amber-100/70 focus:border-[rgb(var(--club-brand))] transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-50 hover:border-amber-200 focus:outline-none focus:text-slate-800 focus:bg-slate-50 focus:border-amber-200 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
