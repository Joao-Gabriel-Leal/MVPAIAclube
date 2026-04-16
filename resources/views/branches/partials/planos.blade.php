<div class="space-y-6">
    <section class="panel p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="section-title">Planos no contexto da filial</div>
                <h2 class="mt-2 text-2xl font-semibold text-slate-950">Planos usados ou ligados aos recursos desta unidade</h2>
            </div>
            @can('create', \App\Models\Plan::class)
                <a href="{{ route('plans.create') }}" class="btn-primary">Novo plano</a>
            @endcan
        </div>

        <div class="mt-6 grid gap-5 xl:grid-cols-2">
            @forelse ($plans as $plan)
                <article class="panel-muted p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="{{ $plan->is_active ? 'chip-success' : 'chip-warning' }}">{{ $plan->is_active ? 'Ativo' : 'Inativo' }}</span>
                                <span class="chip bg-white text-slate-600">{{ $plan->slug }}</span>
                            </div>
                            <h3 class="mt-3 text-2xl font-semibold text-slate-900">{{ $plan->name }}</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ $plan->description ?: 'Sem descricao para este plano.' }}</p>
                        </div>

                        @can('update', $plan)
                            <a href="{{ route('plans.edit', $plan) }}" class="btn-secondary">Editar</a>
                        @endcan
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-3 text-sm text-slate-700">
                        <div><span class="font-semibold text-slate-900">{{ $plan->branch_members_count }}</span><br>associados nesta filial</div>
                        <div><span class="font-semibold text-slate-900">{{ $plan->branch_resources_count }}</span><br>recursos desta filial</div>
                        <div><span class="font-semibold text-slate-900">{{ $plan->dependent_limit }}</span><br>dependentes permitidos</div>
                    </div>

                    @if ($plan->resources->isNotEmpty())
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($plan->resources->take(5) as $resource)
                                <span class="chip-info">{{ $resource->name }}</span>
                            @endforeach
                        </div>
                    @endif
                </article>
            @empty
                <div class="empty-state xl:col-span-2">Nenhum plano ligado ao contexto desta filial.</div>
            @endforelse
        </div>
    </section>
</div>
