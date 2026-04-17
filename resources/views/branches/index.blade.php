<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-4xl">
                <div class="section-title">Filiais</div>
                <h1 class="display-title mt-3">Central por unidade</h1>
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
                        'backgroundColor' => 'rgba(41, 88, 184, 0.74)',
                        'borderRadius' => 12,
                        'maxBarThickness' => 34,
                    ],
                    [
                        'label' => 'Recursos',
                        'data' => $branches->getCollection()->pluck('resources_count')->all(),
                        'backgroundColor' => 'rgba(242, 207, 47, 0.72)',
                        'borderRadius' => 12,
                        'maxBarThickness' => 34,
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
            <div class="metric-value">{{ $summary['branches'] }}</div>
        </div>
        <div class="stat-card">
            <div class="section-title">Ativas</div>
            <div class="metric-value">{{ $summary['active'] }}</div>
        </div>
        <div class="stat-card">
            <div class="section-title">Associados</div>
            <div class="metric-value">{{ $summary['members'] }}</div>
        </div>
        <div class="stat-card">
            <div class="section-title">Pendencias</div>
            <div class="metric-value">{{ $summary['pending_members'] }}</div>
        </div>
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
                        <h2 class="mt-3 text-xl font-semibold text-slate-900">{{ $branch->name }}</h2>
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
                    <div class="metric-panel">
                        <div class="metric-label">Associados ativos</div>
                        <div class="mt-2 text-xl font-semibold tracking-tight text-slate-900">{{ $branch->active_members_count }}</div>
                    </div>
                    <div class="metric-panel">
                        <div class="metric-label">Pendentes</div>
                        <div class="mt-2 text-xl font-semibold tracking-tight text-slate-900">{{ $branch->pending_members_count }}</div>
                    </div>
                    <div class="metric-panel">
                        <div class="metric-label">Dependentes</div>
                        <div class="mt-2 text-xl font-semibold tracking-tight text-slate-900">{{ $branch->dependents_count }}</div>
                    </div>
                    <div class="metric-panel">
                        <div class="metric-label">Recursos</div>
                        <div class="mt-2 text-xl font-semibold tracking-tight text-slate-900">{{ $branch->resources_count }}</div>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    <section class="panel mt-6 p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="section-title">Comparativo</div>
                <h2 class="mt-2 text-xl font-semibold text-slate-950">Filiais</h2>
            </div>
            <div class="chip-info">Matriz</div>
        </div>

        <div class="chart-shell chart-shell-compact chart-shell-mini mt-5">
            <canvas data-chart-source="{{ $branchChartId }}"></canvas>
        </div>
        <script id="{{ $branchChartId }}" type="application/json">@json($branchChart)</script>
    </section>

    <div class="mt-6">
        {{ $branches->links() }}
    </div>
</x-app-layout>
