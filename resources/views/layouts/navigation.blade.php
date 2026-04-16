@props(['mobile' => false])

@php($user = auth()->user())
@php($user->loadMissing(['branch', 'member.primaryBranch', 'dependent.branch']))
@php($isAdminMatrix = $user->isAdminMatrix())
@php($isAdminBranch = $user->isAdminBranch())
@php($adminLinks = $isAdminMatrix
    ? [
        ['label' => 'Filiais', 'route' => route('filiais.index'), 'active' => request()->routeIs('filiais.*') && ! request()->routeIs('profile.*')],
        ['label' => 'Associados', 'route' => route('membros.index'), 'active' => request()->routeIs('membros.*', 'dependentes.*')],
        ['label' => 'Relatorios', 'route' => route('reports.index'), 'active' => request()->routeIs('reports.*')],
        ['label' => 'Configuracoes', 'route' => route('club-settings.edit'), 'active' => request()->routeIs('club-settings.*')],
    ]
    : ($isAdminBranch
        ? [
            ['label' => 'Minha filial', 'route' => route('filiais.show', $user->branch), 'active' => request()->routeIs('filiais.show')],
            ['label' => 'Associados', 'route' => route('membros.index'), 'active' => request()->routeIs('membros.*', 'dependentes.*')],
        ]
        : [
            ['label' => 'Dashboard', 'route' => route('dashboard'), 'active' => request()->routeIs('dashboard')],
            ['label' => 'Reservas', 'route' => route('reservas.index'), 'active' => request()->routeIs('reservas.*')],
        ]))
@php($homeRoute = $isAdminMatrix ? route('filiais.index') : ($isAdminBranch ? route('filiais.show', $user->branch) : route('dashboard')))
@php($contextTitle = $isAdminMatrix
    ? 'Operacao por filial'
    : ($isAdminBranch
        ? ($user->branch?->name ?? 'Minha filial')
        : ($user->activeMember()?->primaryBranch?->name ?? $user->dependent?->branch?->name ?? 'Minha conta')))
@php($contextDescription = $isAdminMatrix
    ? 'Escolha uma filial para abrir planos, recursos, reservas, financeiro e relatorios no contexto certo.'
    : ($isAdminBranch
        ? 'Tudo o que voce gerencia fica agrupado dentro da sua filial.'
        : 'Acompanhe sua conta, reservas e dados principais em um fluxo mais direto.'))
@php($contextSummary = $isAdminMatrix
    ? 'Navegue por unidades e entre no contexto certo.'
    : ($isAdminBranch
        ? 'Gerencie os principais fluxos desta filial.'
        : 'Acesse sua conta e acompanhe reservas com mais clareza.'))
@php($navigationTitle = $isAdminMatrix ? 'Central de gestao' : ($isAdminBranch ? 'Operacao da filial' : 'Minha area'))

<nav class="sidebar-panel {{ $mobile ? 'sidebar-panel-mobile' : '' }}">
    <div class="sidebar-panel__inner">
        <div class="flex items-start justify-between gap-4">
            <a href="{{ $homeRoute }}" class="min-w-0">
                <x-application-logo />
            </a>

            @if ($mobile)
                <button type="button" class="sidebar-close" @click="sidebarOpen = false">
                    <span class="sr-only">Fechar menu</span>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M6 6l12 12"></path>
                        <path d="M18 6L6 18"></path>
                    </svg>
                </button>
            @endif
        </div>

        <div class="sidebar-context">
            <div class="nav-section-label">Contexto atual</div>
            <div class="mt-2 text-base font-bold text-slate-950">{{ $contextTitle }}</div>
            <p class="mt-2 text-sm leading-6 text-slate-600">{{ $contextSummary }}</p>
        </div>

        <div class="sidebar-nav-section">
            <div class="nav-section-label">{{ $navigationTitle }}</div>

            <div class="sidebar-nav">
                @foreach ($adminLinks as $link)
                    <a href="{{ $link['route'] }}" class="{{ $link['active'] ? 'sidebar-link-active' : 'sidebar-link-idle' }}">
                        <span>{{ $link['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="sidebar-footer">
            <div class="sidebar-user-card">
                <div class="nav-section-label">Sessao</div>
                <div class="mt-2 text-sm font-bold text-slate-950">{{ $user->name }}</div>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <span class="chip-brand">{{ $user->role->label() }}</span>
                </div>
                <p class="mt-3 text-sm leading-6 text-slate-600">{{ $contextDescription }}</p>
            </div>

            <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.*') ? 'sidebar-link-active' : 'sidebar-link-idle' }}">
                <span>Perfil</span>
            </a>

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit" class="btn-primary w-full">Sair</button>
            </form>
        </div>
    </div>
</nav>
