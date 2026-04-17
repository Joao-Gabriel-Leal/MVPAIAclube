@php($resourceSections = \App\Support\ResourceTypeCatalog::sections($resources))

<div class="space-y-6">
    <section class="panel p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="section-title">Recursos da filial</div>
                <h2 class="mt-2 text-2xl font-semibold text-slate-950">Tudo o que pode ser reservado nesta unidade</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">Os recursos agora aparecem por tipo para a leitura ficar mais rapida e menos misturada.</p>
            </div>
            @can('create', \App\Models\ClubResource::class)
                <a href="{{ route('recursos.create', ['branch_id' => $branch->id]) }}" class="btn-primary">Novo recurso</a>
            @endcan
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-3">
            <div class="panel-muted p-4">
                <div class="section-title">Recursos</div>
                <div class="mt-2 text-2xl font-semibold text-slate-950">{{ $resources->count() }}</div>
            </div>
            <div class="panel-muted p-4">
                <div class="section-title">Ativos</div>
                <div class="mt-2 text-2xl font-semibold text-slate-950">{{ $resources->where('is_active', true)->count() }}</div>
            </div>
            <div class="panel-muted p-4">
                <div class="section-title">Tipos</div>
                <div class="mt-2 text-2xl font-semibold text-slate-950">{{ $resourceSections->count() }}</div>
            </div>
        </div>

        <div class="mt-6 space-y-5">
            @forelse ($resourceSections as $section)
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
                                    @can('update', $resource)
                                        <a href="{{ route('recursos.edit', $resource) }}" class="btn-secondary">Editar</a>
                                    @endcan
                                </div>

                                <p class="mt-4 text-sm leading-6 text-slate-600">{{ $resource->description ?: 'Sem descricao cadastrada.' }}</p>

                                <div class="mt-5 grid gap-3 text-sm text-slate-700 sm:grid-cols-2">
                                    <div>Capacidade maxima: {{ $resource->max_capacity }}</div>
                                    <div>Valor padrao: R$ {{ number_format((float) $resource->default_price, 2, ',', '.') }}</div>
                                    <div>Horarios configurados: {{ $resource->schedules->count() }}</div>
                                    <div>Planos ligados: {{ $resource->plans->count() }}</div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @empty
                <div class="empty-state">Nenhum recurso cadastrado para esta filial.</div>
            @endforelse
        </div>
    </section>
</div>
