<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-4xl">
                <div class="section-title">Operacao por filial</div>
                <h1 class="display-title mt-3">Escolha uma filial para abrir toda a operacao no contexto certo</h1>
                <p class="lead-text mt-3">
                    A matriz agora navega de forma mais enxuta: entra por filial, compara unidades com mais clareza e acessa planos, recursos, reservas, financeiro e relatorios dentro da propria central.
                </p>
            </div>

            <a href="{{ route('filiais.create') }}" class="btn-primary">Nova filial</a>
        </div>
    </x-slot>

    @php
        $branchChartId = 'branches-overview-chart';
        $branchChart = [
            'type' => 'bar',
            'data' => [
                'labels' => $branches->getCollection()->pluck('name')->all(),
                'datasets' => [
                    [
                        'label' => 'Associados ativos',
                        'data' => $branches->getCollection()->pluck('active_members_count')->all(),
                        'backgroundColor' => 'rgba(124, 58, 237, 0.78)',
                        'borderRadius' => 12,
                    ],
                    [
                        'label' => 'Recursos',
                        'data' => $branches->getCollection()->pluck('resources_count')->all(),
                        'backgroundColor' => 'rgba(16, 185, 129, 0.78)',
                        'borderRadius' => 12,
                    ],
                ],
            ],
            'options' => [
                'plugins' => [
                    'legend' => ['position' => 'bottom'],
                ],
                'scales' => [
                    'y' => ['beginAtZero' => true],
                    'x' => ['grid' => ['display' => false]],
                ],
            ],
        ];
    @endphp

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="stat-card">
            <div class="section-title">Filiais</div>
            <div class="mt-3 text-4xl font-bold text-slate-950">{{ $summary['branches'] }}</div>
            <p class="mt-2 text-sm text-slate-600">Unidades cadastradas no ecossistema.</p>
        </div>
        <div class="stat-card">
            <div class="section-title">Ativas</div>
            <div class="mt-3 text-4xl font-bold text-slate-950">{{ $summary['active'] }}</div>
            <p class="mt-2 text-sm text-slate-600">Filiais prontas para operacao.</p>
        </div>
        <div class="stat-card">
            <div class="section-title">Associados</div>
            <div class="mt-3 text-4xl font-bold text-slate-950">{{ $summary['members'] }}</div>
            <p class="mt-2 text-sm text-slate-600">Base titular distribuida entre as unidades.</p>
        </div>
        <div class="stat-card">
            <div class="section-title">Recursos</div>
            <div class="mt-3 text-4xl font-bold text-slate-950">{{ $summary['resources'] }}</div>
            <p class="mt-2 text-sm text-slate-600">Ambientes e ativos reservaveis no total.</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
        <section class="panel p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="section-title">Leitura executiva</div>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-950">Comparativo visual entre filiais</h2>
                </div>
                <div class="chip-info">Matriz</div>
            </div>

            <div class="chart-shell mt-6">
                <canvas data-chart-source="{{ $branchChartId }}"></canvas>
            </div>
            <script id="{{ $branchChartId }}" type="application/json">@json($branchChart)</script>
        </section>

        <section class="panel p-6">
            <div class="section-title">Como navegar</div>
            <h2 class="mt-2 text-2xl font-semibold text-slate-950">Fluxo resumido por unidade</h2>
            <div class="mt-6 space-y-3">
                <div class="panel-muted p-4">
                    <div class="font-semibold text-slate-900">1. Escolha a filial</div>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Os cards abaixo viraram a porta de entrada operacional da matriz.</p>
                </div>
                <div class="panel-muted p-4">
                    <div class="font-semibold text-slate-900">2. Entre na central da filial</div>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Resumo, planos, recursos, reservas, financeiro e relatorios ficam agrupados no mesmo lugar.</p>
                </div>
                <div class="panel-muted p-4">
                    <div class="font-semibold text-slate-900">3. Use Associados para busca global</div>
                    <p class="mt-2 text-sm leading-6 text-slate-600">A busca principal continua separada apenas para localizar titulares e dependentes por nome, CPF, telefone e e-mail.</p>
                </div>
            </div>
        </section>
    </div>

    <div class="mt-6 grid gap-5 xl:grid-cols-2">
        @foreach ($branches as $branch)
            <article class="panel p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="{{ $branch->type->value === 'headquarters' ? 'chip-info' : 'chip-warning' }}">{{ $branch->type->label() }}</span>
                            <span class="{{ $branch->is_active ? 'chip-success' : 'chip-danger' }}">
                                {{ $branch->is_active ? 'Ativa' : 'Inativa' }}
                            </span>
                        </div>
                        <h2 class="mt-3 text-2xl font-semibold text-slate-900">{{ $branch->name }}</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $branch->address ?: 'Endereco nao informado.' }}</p>
                        <div class="mt-3 flex flex-wrap gap-2 text-xs">
                            @if ($branch->phone)
                                <span class="chip-brand">{{ $branch->phone }}</span>
                            @endif
                            @if ($branch->email)
                                <span class="chip-info">{{ $branch->email }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('filiais.show', $branch) }}" class="btn-primary">Abrir central</a>
                        <a href="{{ route('filiais.edit', $branch) }}" class="btn-secondary">Editar dados</a>
                    </div>
                </div>

                <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Associados ativos</div>
                        <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $branch->active_members_count }}</div>
                    </div>
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Pendentes</div>
                        <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $branch->pending_members_count }}</div>
                    </div>
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Dependentes</div>
                        <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $branch->dependents_count }}</div>
                    </div>
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Recursos</div>
                        <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $branch->resources_count }}</div>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $branches->links() }}
    </div>
</x-app-layout>
