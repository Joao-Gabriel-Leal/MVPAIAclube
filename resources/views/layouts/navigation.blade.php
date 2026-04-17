@props(['mobile' => false])

@php($user = auth()->user())
@php($user->loadMissing(['branch', 'member.primaryBranch', 'dependent.branch', 'dependent.member']))
@php($isAdminMatrix = $user->isAdminMatrix())
@php($isAdminBranch = $user->isAdminBranch())
@php($currentMember = $user->activeMember())
@php($navLinks = $isAdminMatrix
    ? [
        ['label' => 'Filiais', 'route' => route('filiais.index'), 'active' => request()->routeIs('filiais.*') && ! request()->routeIs('profile.*')],
        ['label' => 'Propostas', 'route' => route('proposals.index'), 'active' => request()->routeIs('proposals.*')],
        ['label' => 'Associados', 'route' => route('membros.index'), 'active' => request()->routeIs('membros.*', 'dependentes.*')],
        ['label' => 'Estoque', 'route' => route('inventory.index'), 'active' => request()->routeIs('inventory.*')],
        ['label' => 'Relatorios', 'route' => route('reports.index'), 'active' => request()->routeIs('reports.*')],
        ['label' => 'Configuracoes', 'route' => route('club-settings.edit'), 'active' => request()->routeIs('club-settings.*')],
    ]
    : ($isAdminBranch
        ? [
            ['label' => 'Minha filial', 'route' => route('filiais.show', $user->branch), 'active' => request()->routeIs('filiais.show')],
            ['label' => 'Propostas', 'route' => route('proposals.index'), 'active' => request()->routeIs('proposals.*')],
            ['label' => 'Associados', 'route' => route('membros.index'), 'active' => request()->routeIs('membros.*', 'dependentes.*')],
            ['label' => 'Estoque', 'route' => route('inventory.index'), 'active' => request()->routeIs('inventory.*')],
        ]
        : [
            ['label' => 'Dashboard', 'route' => route('dashboard'), 'active' => request()->routeIs('dashboard')],
            ['label' => 'Faturas', 'route' => route('member-invoices.index'), 'active' => request()->routeIs('member-invoices.*')],
            ['label' => 'Reservas', 'route' => route('reservas.index'), 'active' => request()->routeIs('reservas.*')],
            ['label' => 'Dependentes', 'route' => $currentMember ? route('membros.show', $currentMember).'#dependentes' : route('dashboard'), 'active' => request()->routeIs('membros.show', 'dependentes.*')],
            ['label' => 'Beneficios', 'route' => route('benefits.index'), 'active' => request()->routeIs('benefits.*')],
            ['label' => 'Perfil', 'route' => route('profile.edit'), 'active' => request()->routeIs('profile.*')],
        ]))
@php($homeRoute = $isAdminMatrix ? route('filiais.index') : ($isAdminBranch ? route('filiais.show', $user->branch) : route('dashboard')))
@php($contextTitle = $isAdminMatrix
    ? 'Matriz'
    : ($isAdminBranch
        ? ($user->branch?->name ?? 'Minha filial')
        : ($user->activeMember()?->primaryBranch?->name ?? $user->dependent?->branch?->name ?? 'Minha conta')))
@php($navigationTitle = $isAdminMatrix ? 'Gestao' : ($isAdminBranch ? 'Filial' : 'Minha area'))
@php($contextSubtitle = $isAdminMatrix
    ? 'Rede completa'
    : ($isAdminBranch
        ? 'Operacao local'
        : $user->role->label()))

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
            <div class="sidebar-context__eyebrow">
                <div class="nav-section-label">Contexto atual</div>
                <div class="sidebar-context__badge">{{ $navigationTitle }}</div>
            </div>
            <div class="sidebar-context__title">{{ $contextTitle }}</div>
            <div class="sidebar-context__subtitle">{{ $contextSubtitle }}</div>
        </div>

        <div class="sidebar-nav-section">
            <div class="nav-section-label">{{ $navigationTitle }}</div>

            <div class="sidebar-nav">
                @foreach ($navLinks as $link)
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
            </div>

            @if ($isAdminMatrix || $isAdminBranch)
                <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.*') ? 'sidebar-link-active' : 'sidebar-link-idle' }}">
                    <span>Perfil</span>
                </a>
            @endif

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit" class="btn-primary w-full">Sair</button>
            </form>
        </div>
    </div>
</nav>
