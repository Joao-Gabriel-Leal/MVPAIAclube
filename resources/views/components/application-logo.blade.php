@props(['theme' => 'light'])

@php($brandName = 'ClubeAIA')
@php($isDark = $theme === 'dark')

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-3']) }}>
    <div class="relative flex h-12 w-12 items-center justify-center rounded-[1.4rem] text-lg font-black text-white shadow-[0_18px_30px_-18px_rgba(15,23,42,0.55)]">
        <div class="absolute inset-0 rounded-[1.4rem] bg-[linear-gradient(145deg,#173554,#0f2740)]"></div>
        <div class="absolute inset-[1px] rounded-[1.32rem] border border-white/10"></div>
        <span class="relative">A</span>
    </div>

    <div>
        <div class="text-[0.7rem] font-extrabold uppercase tracking-[0.34em] {{ $isDark ? 'text-slate-300' : 'text-slate-500' }}">Clube</div>
        <div class="text-lg font-bold {{ $isDark ? 'text-white' : 'text-slate-950' }}">{{ $brandName }}</div>
    </div>
</div>
