<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>ClubeAIA</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800|fraunces:500,600,700" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        @php($firstBranch = $branches->first())

        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <header class="flex flex-col gap-4 py-3 md:flex-row md:items-center md:justify-between">
                <x-application-logo />

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <nav class="flex flex-wrap gap-5 text-sm font-semibold text-slate-600">
                        <a href="#sobre" class="transition hover:text-slate-950">Sobre</a>
                        <a href="#galeria" class="transition hover:text-slate-950">Fotos</a>
                        <a href="#unidades" class="transition hover:text-slate-950">Unidades</a>
                        <a href="#planos" class="transition hover:text-slate-950">Planos</a>
                    </nav>

                    <a href="{{ route('login') }}" class="btn-primary">Entrar</a>
                </div>
            </header>

            <section class="club-hero mt-4 overflow-hidden">
                <img src="{{ $heroImage }}" alt="Banner principal do ClubeAIA" class="club-hero__image">
                <div class="club-hero__overlay"></div>

                <div class="club-hero__content">
                    <div class="max-w-2xl">
                        <div class="section-title !text-white/70">ClubeAIA</div>
                        <h1 class="mt-4 text-4xl font-semibold tracking-tight text-white sm:text-6xl">Bem-vindo ao ClubeAIA</h1>
                        <p class="mt-4 max-w-xl text-base leading-7 text-white/90 sm:text-lg">Esporte, lazer e convivio em uma experiencia de clube feita para viver bem.</p>

                        <div class="mt-7 flex flex-wrap gap-3">
                            <a href="#planos" class="btn-primary">Ver planos</a>
                            <a href="#unidades" class="btn-secondary border-white/20 bg-white/10 text-white hover:bg-white/20">Conhecer unidades</a>
                        </div>
                    </div>
                </div>
            </section>

            <section id="sobre" class="mx-auto mt-14 max-w-3xl text-center">
                <div class="section-title">Sobre o clube</div>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">Uma apresentacao mais visual, direta e facil de entender</h2>
                <p class="mt-4 text-base leading-8 text-slate-600">{{ $aboutText }}</p>
            </section>

            <section id="galeria" class="mt-16">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div class="max-w-2xl">
                        <div class="section-title">Galeria</div>
                        <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">Veja o clima do clube antes de qualquer explicacao longa</h2>
                    </div>
                    <p class="max-w-md text-sm leading-7 text-slate-500">Uma selecao visual para mostrar convivio, esporte, lazer e os ambientes que fazem o clube ser rapido de entender.</p>
                </div>

                <div class="club-gallery mt-6">
                    @foreach ($galleryImages as $image)
                        <article class="club-gallery__item {{ $loop->first ? 'club-gallery__item--featured' : '' }}">
                            <img src="{{ $image['src'] }}" alt="{{ $image['alt'] }}" class="club-gallery__image">
                            <div class="club-gallery__caption">
                                <span>{{ $image['title'] }}</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section id="unidades" class="mt-16">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div class="max-w-2xl">
                        <div class="section-title">Unidades</div>
                        <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">Escolha a unidade mais proxima do seu ritmo</h2>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-3">
                    @forelse ($branches as $branch)
                        <article class="club-unit-card">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $branch['city'] }}</p>
                                <h3 class="mt-3 text-2xl font-semibold text-slate-950">{{ $branch['name'] }}</h3>
                                <p class="mt-3 text-sm leading-6 text-slate-600">{{ $branch['address'] }}</p>
                            </div>

                            <a href="{{ route('enrollment.create', $branch['model']) }}" class="btn-secondary mt-6 w-full">Ver unidade</a>
                        </article>
                    @empty
                        <div class="panel p-8 md:col-span-3">
                            <div class="section-title">Sem unidades ativas</div>
                            <p class="mt-3 text-sm leading-7 text-slate-600">As unidades aparecerao aqui assim que o clube disponibilizar novas entradas de adesao.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section id="planos" class="mt-16">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div class="max-w-2xl">
                        <div class="section-title">Planos</div>
                        <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">Planos claros para decidir sem excesso de leitura</h2>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 lg:grid-cols-3">
                    @forelse ($plans as $plan)
                        <article class="club-plan-card {{ $plan['recommended'] ? 'club-plan-card--recommended' : '' }}">
                            @if ($plan['recommended'])
                                <div class="club-plan-card__badge">Mais equilibrado</div>
                            @endif

                            <div class="section-title {{ $plan['recommended'] ? '!text-white/75' : '' }}">Plano {{ $plan['slug'] }}</div>
                            <h3 class="mt-3 text-2xl font-semibold {{ $plan['recommended'] ? 'text-white' : 'text-slate-950' }}">{{ $plan['name'] }}</h3>
                            <p class="mt-3 text-4xl font-semibold {{ $plan['recommended'] ? 'text-white' : 'text-slate-950' }}">
                                R$ {{ number_format((float) $plan['price'], 2, ',', '.') }}
                            </p>

                            <div class="mt-6 space-y-3">
                                @foreach ($plan['benefits'] as $benefit)
                                    <div class="club-plan-card__benefit {{ $plan['recommended'] ? 'club-plan-card__benefit--recommended' : '' }}">
                                        {{ $benefit }}
                                    </div>
                                @endforeach
                            </div>

                            @if ($firstBranch)
                                <a href="{{ route('enrollment.create', $firstBranch['model']) }}" class="{{ $plan['recommended'] ? 'btn-secondary border-white/20 bg-white/10 text-white hover:bg-white/20' : 'btn-primary' }} mt-6 w-full">Iniciar adesao</a>
                            @endif
                        </article>
                    @empty
                        <div class="panel p-8 lg:col-span-3">
                            <div class="section-title">Sem planos ativos</div>
                            <p class="mt-3 text-sm leading-7 text-slate-600">Os planos aparecerao aqui assim que novas opcoes de adesao estiverem disponiveis.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="mt-16">
                <div class="panel-dark overflow-hidden">
                    <div class="relative grid gap-6 p-8 sm:p-10 lg:grid-cols-[1fr_auto] lg:items-center">
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(255,255,255,0.09),_transparent_28%),radial-gradient(circle_at_bottom_right,_rgba(196,167,112,0.12),_transparent_24%)]"></div>

                        <div class="relative max-w-2xl">
                            <div class="section-title !text-white/70">ClubeAIA</div>
                            <h2 class="mt-3 text-3xl font-semibold tracking-tight text-white">Entenda o clube, veja as fotos e entre quando quiser</h2>
                            <p class="mt-4 text-sm leading-7 text-slate-200/80">Em poucos segundos, a home mostra o essencial: atmosfera, unidades, planos e o caminho para acessar o portal.</p>
                        </div>

                        <div class="relative flex flex-wrap gap-3 lg:justify-end">
                            <a href="{{ route('login') }}" class="btn-primary">Entrar</a>
                            @if ($firstBranch)
                                <a href="{{ route('enrollment.create', $firstBranch['model']) }}" class="btn-secondary border-white/20 bg-white/10 text-white hover:bg-white/20">Iniciar adesao</a>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </body>
</html>
