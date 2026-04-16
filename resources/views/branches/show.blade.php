<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
            <div class="max-w-4xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="{{ $branch->type->value === 'headquarters' ? 'chip-info' : 'chip-warning' }}">{{ $branch->type->label() }}</span>
                    <span class="{{ $branch->is_active ? 'chip-success' : 'chip-danger' }}">
                        {{ $branch->is_active ? 'Filial ativa' : 'Filial inativa' }}
                    </span>
                </div>
                <h1 class="display-title mt-3">{{ $branch->name }}</h1>
                <p class="lead-text mt-3">
                    Central da filial com uma operacao mais enxuta: resumo, planos, recursos, reservas, financeiro e relatorios no mesmo fluxo.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                @can('update', $branch)
                    <a href="{{ route('filiais.edit', $branch) }}" class="btn-secondary">Editar filial</a>
                @endcan
                @can('create', \App\Models\ClubResource::class)
                    <a href="{{ route('recursos.create', ['branch_id' => $branch->id]) }}" class="btn-secondary">Novo recurso</a>
                @endcan
                <a href="{{ route('reservas.create', ['branch_id' => $branch->id]) }}" class="btn-primary">Nova reserva</a>
            </div>
        </div>
    </x-slot>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($summaryCards as $label => $value)
            <div class="stat-card">
                <div class="section-title">{{ $label }}</div>
                <div class="mt-3 text-4xl font-bold text-slate-950">
                    @if (is_float($value))
                        R$ {{ number_format($value, 2, ',', '.') }}
                    @else
                        {{ $value }}
                    @endif
                </div>
                <p class="mt-2 text-sm text-slate-600">Leitura operacional desta filial.</p>
            </div>
        @endforeach
    </div>

    <div class="panel-dark mt-6 overflow-hidden p-6">
        <div class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr] xl:items-center">
            <div>
                <div class="section-title text-violet-200">Contexto da unidade</div>
                <h2 class="mt-3 text-3xl font-semibold text-white">A operacao deixa de ficar espalhada e passa a viver dentro da filial</h2>
                <p class="mt-3 max-w-2xl text-sm leading-7 text-violet-50/90">
                    {{ $branch->address ?: 'Endereco nao informado.' }}{{ $branch->phone ? ' - '.$branch->phone : '' }}{{ $branch->email ? ' - '.$branch->email : '' }}
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-3 xl:grid-cols-1">
                <div class="branch-highlight-card">
                    <div class="text-sm font-semibold uppercase tracking-[0.22em] text-violet-100">Associados</div>
                    <div class="mt-2 text-3xl font-bold">{{ $highlights['members'] }}</div>
                    <p class="mt-2 text-sm text-violet-100/80">Base vinculada diretamente a esta unidade.</p>
                </div>
                <div class="branch-highlight-card">
                    <div class="text-sm font-semibold uppercase tracking-[0.22em] text-violet-100">Pendencias</div>
                    <div class="mt-2 text-3xl font-bold">{{ $highlights['pendingMembers'] + $highlights['pendingDependents'] }}</div>
                    <p class="mt-2 text-sm text-violet-100/80">Itens aguardando aprovacao ou acompanhamento.</p>
                </div>
                <div class="branch-highlight-card">
                    <div class="text-sm font-semibold uppercase tracking-[0.22em] text-violet-100">Planos no contexto</div>
                    <div class="mt-2 text-3xl font-bold">{{ $highlights['plans'] }}</div>
                    <p class="mt-2 text-sm text-violet-100/80">Planos ligados aos associados ou recursos da filial.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="branch-tab-nav mt-6">
        @foreach ($tabs as $tabKey => $tabLabel)
            <a href="{{ route('filiais.show', ['branch' => $branch, 'tab' => $tabKey]) }}" class="{{ $tab === $tabKey ? 'branch-tab-link-active' : 'branch-tab-link' }}">
                {{ $tabLabel }}
            </a>
        @endforeach
    </div>

    <div class="mt-6">
        @include("branches.partials.$tab")
    </div>
</x-app-layout>
