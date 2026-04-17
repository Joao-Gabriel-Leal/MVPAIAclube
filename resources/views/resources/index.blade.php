<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="max-w-4xl">
                <div class="section-title">Agenda e recursos</div>
                <h1 class="display-title mt-2">Recursos organizados por filial e tipo</h1>
                <p class="lead-text mt-3">
                    A visualizacao reforca o contexto da filial, separa tipos de uso e deixa a operacao mais clara para administracao e reservas.
                </p>
            </div>
            <a href="{{ route('recursos.create') }}" class="btn-primary">Novo recurso</a>
        </div>
    </x-slot>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        <div class="stat-card xl:col-span-1">
            <div class="section-title">Recursos</div>
            <div class="metric-value">{{ $summary['resources'] }}</div>
            <p class="mt-2 text-sm text-slate-600">Itens dentro do recorte atual.</p>
        </div>
        <div class="stat-card xl:col-span-1">
            <div class="section-title">Ativos</div>
            <div class="metric-value">{{ $summary['active'] }}</div>
            <p class="mt-2 text-sm text-slate-600">Prontos para reserva.</p>
        </div>
        <div class="stat-card xl:col-span-1">
            <div class="section-title">Filiais</div>
            <div class="metric-value">{{ $summary['branches'] }}</div>
            <p class="mt-2 text-sm text-slate-600">Unidades representadas na tela.</p>
        </div>
        <div class="stat-card xl:col-span-1">
            <div class="section-title">Tipos</div>
            <div class="metric-value">{{ $typeOptions->count() }}</div>
            <p class="mt-2 text-sm text-slate-600">Grupos disponiveis para organizar a agenda.</p>
        </div>
        <div class="stat-card xl:col-span-1">
            <div class="section-title">Bloqueios</div>
            <div class="metric-value">{{ $summary['blocked'] }}</div>
            <p class="mt-2 text-sm text-slate-600">Bloqueios cadastrados nesse escopo.</p>
        </div>
    </div>

    <div class="panel mt-6 p-6">
        <form method="GET" class="grid gap-4 xl:grid-cols-[1.3fr_1fr_1fr_auto]">
            <div>
                <label class="field-label" for="q">Buscar recurso</label>
                <input
                    class="field-input"
                    id="q"
                    name="q"
                    type="text"
                    value="{{ $search }}"
                    placeholder="Nome, tipo ou descricao"
                />
            </div>

            <div>
                <label class="field-label" for="branch_id">Filial</label>
                <select class="field-select" name="branch_id" id="branch_id">
                    <option value="">Todas</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="field-label" for="type">Tipo</label>
                <select class="field-select" name="type" id="type">
                    <option value="">Todos os tipos</option>
                    @foreach ($typeOptions as $typeOption)
                        <option value="{{ $typeOption['value'] }}" @selected($typeFilter === $typeOption['value'])>{{ $typeOption['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button class="btn-secondary w-full" type="submit">Atualizar visao</button>
            </div>
        </form>

        <div class="context-card mt-4">
            <div class="nav-section-label">Contexto de exibicao</div>
            <div class="mt-1 text-base font-bold text-slate-900">{{ $selectedBranch?->name ?? 'Visao consolidada de filiais' }}</div>
            <p class="mt-1 text-sm text-slate-600">
                {{ $selectedBranch ? 'Os recursos abaixo pertencem a esta filial e seguem separados por tipo para a leitura ficar mais objetiva.' : 'Sem filtro de filial, os recursos continuam agrupados por unidade e por tipo para evitar mistura visual.' }}
            </p>
        </div>
    </div>

    @if ($branchSummaries->isNotEmpty())
        <div class="mt-6 grid gap-4 xl:grid-cols-3">
            @foreach ($branchSummaries as $branchSummary)
                <div class="panel p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="section-title">Resumo da filial</div>
                            <h2 class="mt-3 text-xl font-semibold text-slate-950">{{ $branchSummary['name'] }}</h2>
                        </div>
                        @if ($branchSummary['id'])
                            <a href="{{ route('filiais.show', ['branch' => $branchSummary['id'], 'tab' => 'recursos']) }}" class="inline-link">Abrir central</a>
                        @endif
                    </div>
                    <div class="mt-4 grid gap-3 text-sm text-slate-700 sm:grid-cols-3">
                        <div><span class="font-semibold text-slate-900">{{ $branchSummary['resources'] }}</span><br>recursos</div>
                        <div><span class="font-semibold text-slate-900">{{ $branchSummary['active'] }}</span><br>ativos</div>
                        <div><span class="font-semibold text-slate-900">{{ $branchSummary['types'] }}</span><br>tipos</div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @php($groupedResources = $resources->getCollection()->groupBy(fn ($resource) => $resource->branch?->name ?? 'Sem filial'))

    <div class="mt-6 space-y-6">
        @forelse ($groupedResources as $branchName => $branchResources)
            @php($typeSections = \App\Support\ResourceTypeCatalog::sections($branchResources))
            <section class="panel p-6">
                <div class="flex flex-col gap-3 border-b border-amber-100/80 pb-5 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="section-title">Filial</div>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-950">{{ $branchName }}</h2>
                        <p class="mt-2 text-sm text-slate-600">Os recursos desta unidade foram agrupados por tipo para facilitar leitura, comparacao e tomada de decisao.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <div class="chip-info">{{ $branchResources->count() }} recurso(s)</div>
                        <div class="chip-brand">{{ $typeSections->count() }} tipo(s)</div>
                    </div>
                </div>

                <div class="mt-6 space-y-5">
                    @foreach ($typeSections as $section)
                        <section class="rounded-[1.55rem] border border-amber-100/80 bg-amber-50/35 p-5">
                            <div class="flex flex-col gap-3 border-b border-amber-100/80 pb-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <div class="section-title">Tipo</div>
                                    <h3 class="mt-2 text-xl font-semibold text-slate-950">{{ $section['label'] }}</h3>
                                </div>
                                <div class="chip-brand">{{ $section['resources']->count() }} recurso(s)</div>
                            </div>

                            <div class="mt-5 grid gap-4 xl:grid-cols-2">
                                @foreach ($section['resources'] as $resource)
                                    <article class="panel-muted p-6">
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <div class="{{ $resource->is_active ? 'chip-success' : 'chip-danger' }}">
                                                    {{ $resource->is_active ? 'Ativo' : 'Inativo' }}
                                                </div>
                                                <h4 class="mt-4 text-2xl font-semibold text-slate-900">{{ $resource->name }}</h4>
                                            </div>
                                            <a href="{{ route('recursos.edit', $resource) }}" class="btn-secondary">Editar</a>
                                        </div>

                                        <p class="mt-4 text-sm leading-6 text-slate-600">{{ $resource->description ?: 'Sem descricao cadastrada.' }}</p>

                                        <div class="mt-5 grid gap-3 text-sm text-slate-700 sm:grid-cols-2">
                                            <div>Capacidade maxima: {{ $resource->max_capacity }}</div>
                                            <div>Valor padrao: R$ {{ number_format((float) $resource->default_price, 2, ',', '.') }}</div>
                                            <div>Horarios configurados: {{ $resource->schedules->count() }}</div>
                                            <div>Planos liberados: {{ $resource->plans->count() }}</div>
                                            <div>Bloqueios cadastrados: {{ $resource->blocks->count() }}</div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="empty-state">Nenhum recurso encontrado para os filtros informados.</div>
        @endforelse
    </div>

    <div class="mt-6">{{ $resources->links() }}</div>
</x-app-layout>
