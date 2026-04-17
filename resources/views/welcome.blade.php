<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $clubSetting->resolvedSeoTitle() }}</title>
        <meta name="description" content="{{ $clubSetting->resolvedSeoDescription() }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>:root { {{ $clubSetting->themeCssVariablesInline() }} }</style>
    </head>
    <body>
        @php($firstBranch = $branches->firstWhere('type', 'branch'))

        <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
            <header class="flex flex-col gap-3 py-2 md:flex-row md:items-center md:justify-between">
                <x-application-logo />

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <nav class="flex flex-wrap gap-4 text-[0.9rem] font-semibold text-slate-600">
                        <a href="#sobre" class="transition hover:text-slate-950">Sobre</a>
                        <a href="#galeria" class="transition hover:text-slate-950">Fotos</a>
                        <a href="#unidades" class="transition hover:text-slate-950">Unidades</a>
                        <a href="#planos" class="transition hover:text-slate-950">Planos</a>
                    </nav>

                    <a href="{{ route('login') }}" class="btn-primary">Entrar</a>
                </div>
            </header>

            <section class="club-hero mt-4 overflow-hidden">
                @if ($heroImageUrl)
                    <img src="{{ $heroImageUrl }}" alt="Banner principal do {{ $brandName }}" class="club-hero__image">
                    <div class="club-hero__overlay"></div>
                @else
                    <div class="club-hero__placeholder">
                        <div class="club-hero__placeholder-badge">Banner aguardando foto oficial</div>
                    </div>
                    <div class="club-hero__overlay club-hero__overlay--placeholder"></div>
                @endif

                <div class="club-hero__content">
                    <div class="max-w-2xl">
                        <div class="section-title !text-white/70">{{ $brandName }}</div>
                        <h1 class="mt-3 text-[2rem] font-semibold tracking-tight text-white sm:text-[2.7rem]">{{ $heroTitle }}</h1>
                        <p class="mt-3 max-w-2xl text-[0.9rem] leading-6 text-white/90 sm:text-[0.98rem]">{{ $heroSubtitle }}</p>

                        <div class="mt-6 flex flex-wrap gap-2.5">
                            <a href="#planos" class="btn-primary">Ver planos</a>
                            <a href="#unidades" class="btn-secondary border-white/30 bg-white/85 text-slate-900 hover:bg-white">Conhecer filiais</a>
                        </div>
                    </div>
                </div>
            </section>

            <section id="sobre" class="mx-auto mt-12 max-w-3xl text-center">
                <div class="section-title">Sobre a rede</div>
                <h2 class="mt-3 text-[1.65rem] font-semibold tracking-tight text-slate-950">{{ $clubSetting->resolvedHomeAboutTitle() }}</h2>
                <p class="mt-3.5 text-[0.9rem] leading-6 text-slate-600">{{ $aboutText }}</p>
            </section>

            <section id="galeria" class="mt-14">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div class="max-w-2xl">
                        <div class="section-title">Galeria</div>
                        <h2 class="mt-3 text-[1.65rem] font-semibold tracking-tight text-slate-950">{{ $clubSetting->resolvedHomeGalleryTitle() }}</h2>
                        @if ($clubSetting->resolvedHomeGallerySubtitle() !== '')
                            <p class="mt-3 text-[0.9rem] leading-6 text-slate-600">{{ $clubSetting->resolvedHomeGallerySubtitle() }}</p>
                        @endif
                    </div>
                </div>

                <div class="club-gallery mt-5">
                    @foreach ($galleryImages as $image)
                        <article class="club-gallery__item {{ $loop->first ? 'club-gallery__item--featured' : '' }}">
                            @if ($image['src'])
                                <img src="{{ $image['src'] }}" alt="{{ $image['alt'] ?: 'Imagem institucional do '.$brandName }}" class="club-gallery__image">
                            @else
                                <div class="club-gallery__placeholder">
                                    <span>{{ $brandName }} - {{ $image['placeholder'] }}</span>
                                </div>
                            @endif
                            <div class="club-gallery__caption">
                                <span>{{ $image['title'] }}</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section id="unidades" class="mt-14">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div class="max-w-2xl">
                        <div class="section-title">Filiais</div>
                        <h2 class="mt-3 text-[1.65rem] font-semibold tracking-tight text-slate-950">{{ $clubSetting->resolvedHomeBranchesTitle() }}</h2>
                        @if ($clubSetting->resolvedHomeBranchesSubtitle() !== '')
                            <p class="mt-3 text-[0.9rem] leading-6 text-slate-600">{{ $clubSetting->resolvedHomeBranchesSubtitle() }}</p>
                        @endif
                    </div>
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @forelse ($branches as $branch)
                        <article class="club-unit-card">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $branch['city'] }}</p>
                                <h3 class="mt-2.5 text-[1.2rem] font-semibold text-slate-950">{{ $branch['name'] }}</h3>
                                <p class="mt-2.5 text-[0.9rem] leading-6 text-slate-600">{{ $branch['address'] }}</p>
                                @if ($branch['summary'])
                                    <p class="mt-2.5 text-[0.88rem] leading-6 text-slate-500">{{ $branch['summary'] }}</p>
                                @endif
                                <div class="mt-3.5 flex flex-wrap gap-2">
                                    @if ($branch['phone'])
                                        @if ($branch['phone_link'])
                                            <a href="{{ $branch['phone_link'] }}" class="chip-brand">{{ $branch['phone'] }}</a>
                                        @else
                                            <span class="chip-brand">{{ $branch['phone'] }}</span>
                                        @endif
                                    @endif
                                    @if ($branch['whatsapp'])
                                        <a href="{{ $branch['whatsapp_link'] }}" class="chip-info" target="_blank" rel="noreferrer">WhatsApp {{ $branch['whatsapp'] }}</a>
                                    @endif
                                    @if ($branch['hours'])
                                        <span class="chip-info">{{ $branch['hours'] }}</span>
                                    @endif
                                    @if ($branch['email'])
                                        <a href="mailto:{{ $branch['email'] }}" class="chip-info">{{ $branch['email'] }}</a>
                                    @endif
                                </div>
                            </div>

                            <a href="{{ route('enrollment.create', $branch['model']) }}" class="btn-secondary mt-5 w-full">Ver filial</a>
                        </article>
                    @empty
                        <div class="panel p-6 md:col-span-3">
                            <div class="section-title">Sem filiais ativas</div>
                            <p class="mt-3 text-sm leading-7 text-slate-600">As filiais aparecerao aqui assim que a rede disponibilizar novas entradas de adesao.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section id="planos" class="mt-14">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div class="max-w-2xl">
                        <div class="section-title">Planos</div>
                        <h2 class="mt-3 text-[1.65rem] font-semibold tracking-tight text-slate-950">{{ $clubSetting->resolvedHomePlansTitle() }}</h2>
                        @if ($clubSetting->resolvedHomePlansSubtitle() !== '')
                            <p class="mt-3 text-[0.9rem] leading-6 text-slate-600">{{ $clubSetting->resolvedHomePlansSubtitle() }}</p>
                        @endif
                    </div>
                </div>

                <div class="mt-5 grid gap-4 lg:grid-cols-3">
                    @forelse ($plans as $plan)
                        <article class="club-plan-card {{ $plan['recommended'] ? 'club-plan-card--recommended' : '' }}">
                            @if ($plan['recommended'])
                                <div class="club-plan-card__badge">Mais equilibrado</div>
                            @endif

                            <div class="section-title {{ $plan['recommended'] ? '!text-slate-600' : '' }}">Plano {{ $plan['slug'] }}</div>
                            <h3 class="mt-2.5 text-[1.2rem] font-semibold text-slate-950">{{ $plan['name'] }}</h3>
                            <p class="mt-2.5 text-[2.2rem] font-semibold text-slate-950">
                                R$ {{ number_format((float) $plan['price'], 2, ',', '.') }}
                            </p>
                            <div class="mt-5 space-y-2.5">
                                @foreach ($plan['benefits'] as $benefit)
                                    <div class="club-plan-card__benefit {{ $plan['recommended'] ? 'club-plan-card__benefit--recommended' : '' }}">
                                        {{ $benefit }}
                                    </div>
                                @endforeach
                            </div>

                            @if ($firstBranch)
                                <a href="{{ route('enrollment.create', $firstBranch['model']) }}" class="btn-primary mt-5 w-full">Iniciar adesao</a>
                            @endif
                        </article>
                    @empty
                        <div class="panel p-6 lg:col-span-3">
                            <div class="section-title">Sem planos ativos</div>
                            <p class="mt-3 text-sm leading-7 text-slate-600">Os planos aparecerao aqui assim que novas opcoes de adesao estiverem disponiveis.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="mt-14">
                <div class="panel-brand-soft overflow-hidden">
                    <div class="relative grid gap-5 p-6 sm:p-7 lg:grid-cols-[1fr_auto] lg:items-center">
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(255,239,168,0.34),_transparent_28%),radial-gradient(circle_at_bottom_right,_rgba(41,88,184,0.08),_transparent_24%)]"></div>

                        <div class="relative max-w-2xl">
                            <div class="section-title">{{ $brandName }}</div>
                            <h2 class="mt-3 text-[1.65rem] font-semibold tracking-tight text-slate-950">{{ $clubSetting->resolvedHomeFinalCtaTitle() }}</h2>
                        </div>

                        <div class="relative flex flex-wrap gap-2.5 lg:justify-end">
                            <a href="{{ route('login') }}" class="btn-primary">Entrar</a>
                            @if ($firstBranch)
                                <a href="{{ route('enrollment.create', $firstBranch['model']) }}" class="btn-secondary">Iniciar adesao</a>
                            @endif
                        </div>
                    </div>
                </div>

                @if ($clubSetting->hasPublicContactChannels())
                    <div class="mt-5 flex flex-wrap gap-2.5">
                        @if ($clubSetting->site_phone)
                            <a href="{{ $clubSetting->sitePhoneLink() }}" class="chip-brand">{{ $clubSetting->site_phone }}</a>
                        @endif
                        @if ($clubSetting->site_whatsapp)
                            <a href="{{ $clubSetting->siteWhatsappLink() }}" class="chip-info" target="_blank" rel="noreferrer">WhatsApp {{ $clubSetting->site_whatsapp }}</a>
                        @endif
                        @if ($clubSetting->site_email)
                            <a href="mailto:{{ $clubSetting->site_email }}" class="chip-info">{{ $clubSetting->site_email }}</a>
                        @endif
                        @if ($clubSetting->instagram_url)
                            <a href="{{ $clubSetting->instagram_url }}" class="chip-info" target="_blank" rel="noreferrer">Instagram</a>
                        @endif
                        @if ($clubSetting->facebook_url)
                            <a href="{{ $clubSetting->facebook_url }}" class="chip-info" target="_blank" rel="noreferrer">Facebook</a>
                        @endif
                    </div>
                @endif
            </section>
        </div>
    </body>
</html>
