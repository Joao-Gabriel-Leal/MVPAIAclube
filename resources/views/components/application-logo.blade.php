@props(['theme' => 'light'])

@php($clubSetting = \App\Models\ClubSetting::current())
@php($brandName = $clubSetting->resolvedBrandName())
@php($isDark = $theme === 'dark')
@php($logoUrl = $clubSetting->logoMedia?->url())

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-3']) }}>
    @if ($logoUrl)
        <div class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-[1.2rem] bg-white/95 shadow-[0_18px_30px_-18px_rgba(15,23,42,0.32)]">
            <img src="{{ $logoUrl }}" alt="Logo {{ $brandName }}" class="h-full w-full object-contain bg-white p-1.5">
        </div>
    @else
        <div class="relative flex h-12 w-12 items-center justify-center rounded-[1.4rem] text-lg font-black text-white shadow-[0_18px_30px_-18px_rgba(15,23,42,0.55)]">
            <div class="absolute inset-0 rounded-[1.4rem] bg-[linear-gradient(145deg,#173554,#0f2740)]"></div>
            <div class="absolute inset-[1px] rounded-[1.32rem] border border-white/10"></div>
            <span class="relative">A</span>
        </div>
    @endif

    <div>
        <div class="text-[0.74rem] font-semibold tracking-[0.08em] {{ $isDark ? 'text-slate-300' : 'text-slate-500' }}">Rede</div>
        <div class="text-lg font-bold {{ $isDark ? 'text-white' : 'text-slate-950' }}">{{ $brandName }}</div>
    </div>
</div>
