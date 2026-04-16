<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="section-title">Configuracao comercial</div>
            <h1 class="display-title mt-2">Planos</h1>
        </div>

        @if ($canManagePlans)
            <a href="{{ route('plans.create') }}" class="btn-primary">Novo plano</a>
        @endif
    </x-slot>

    @php($activePlans = $plans->where('is_active', true)->count())
    @php($linkedMembers = $plans->sum('members_count'))
    @php($linkedResources = $plans->sum('resources_count'))

    <div class="space-y-6">
        @if ($errors->any())
            <div class="panel border-rose-100 bg-rose-50/90 px-5 py-4 text-sm text-rose-700">
                <div class="font-semibold">Nao foi possivel concluir a operacao.</div>
                <div class="mt-1">{{ $errors->first() }}</div>
            </div>
        @endif

        <section class="panel p-6">
            <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr] lg:items-start">
                <div>
                    <div class="section-title">Gestao mais simples</div>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900">Agora cada plano tem um resumo claro e uma tela propria de configuracao.</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-600">
                        Use a lista abaixo para comparar rapidamente preco, limites e recursos liberados. Quando precisar ajustar um plano,
                        abra a configuracao dele para editar com calma, sem varios formularios grandes na mesma tela.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1 xl:grid-cols-3">
                    <div class="panel-muted p-4">
                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Total</div>
                        <div class="mt-2 text-3xl font-semibold text-slate-900">{{ $plans->count() }}</div>
                        <div class="mt-1 text-sm text-slate-500">planos cadastrados</div>
                    </div>
                    <div class="panel-muted p-4">
                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Ativos</div>
                        <div class="mt-2 text-3xl font-semibold text-slate-900">{{ $activePlans }}</div>
                        <div class="mt-1 text-sm text-slate-500">planos liberados para uso</div>
                    </div>
                    <div class="panel-muted p-4">
                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Vinculos</div>
                        <div class="mt-2 text-3xl font-semibold text-slate-900">{{ $linkedMembers }}</div>
                        <div class="mt-1 text-sm text-slate-500">{{ $linkedResources }} recursos distribuidos</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-5 xl:grid-cols-2">
            @forelse ($plans as $plan)
                <article class="panel p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="{{ $plan->is_active ? 'chip-success' : 'chip-warning' }}">
                                    {{ $plan->is_active ? 'Ativo' : 'Inativo' }}
                                </span>
                                <span class="chip bg-slate-100 text-slate-600">{{ $plan->slug }}</span>
                            </div>
                            <h2 class="mt-3 text-2xl font-semibold text-slate-900">{{ $plan->name }}</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                {{ $plan->description ?: 'Sem descricao cadastrada para este plano.' }}
                            </p>
                        </div>

                        @if ($canManagePlans)
                            <a href="{{ route('plans.edit', $plan) }}" class="btn-secondary">Editar configuracao</a>
                        @endif
                    </div>

                    <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Valor base</div>
                            <div class="mt-2 text-lg font-semibold text-slate-900">
                                {{ $plan->base_price !== null ? 'R$ '.number_format((float) $plan->base_price, 2, ',', '.') : 'Nao definido' }}
                            </div>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Dependentes</div>
                            <div class="mt-2 text-lg font-semibold text-slate-900">{{ $plan->dependent_limit }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Convidados</div>
                            <div class="mt-2 text-lg font-semibold text-slate-900">{{ $plan->guest_limit_per_reservation }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Associados</div>
                            <div class="mt-2 text-lg font-semibold text-slate-900">{{ $plan->members_count }}</div>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="section-title">Recursos liberados</div>
                            <div class="text-sm text-slate-500">{{ $plan->resources_count }} vinculados</div>
                        </div>

                        @if ($plan->resources->isEmpty())
                            <p class="text-sm text-slate-500">Nenhum recurso liberado neste plano.</p>
                        @else
                            <div class="flex flex-wrap gap-2">
                                @foreach ($plan->resources->take(6) as $resource)
                                    <span class="chip chip-info">{{ $resource->name }} - {{ $resource->branch->name }}</span>
                                @endforeach

                                @if ($plan->resources_count > 6)
                                    <span class="chip bg-slate-100 text-slate-600">+{{ $plan->resources_count - 6 }} recursos</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </article>
            @empty
                <div class="panel p-10 text-center xl:col-span-2">
                    <div class="section-title">Nenhum plano</div>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900">Ainda nao existem planos cadastrados.</h2>
                    <p class="mt-3 text-sm text-slate-600">
                        Cadastre o primeiro plano para definir preco, limites e recursos disponiveis para os associados.
                    </p>

                    @if ($canManagePlans)
                        <a href="{{ route('plans.create') }}" class="btn-primary mt-6">Cadastrar primeiro plano</a>
                    @endif
                </div>
            @endforelse
        </section>
    </div>
</x-app-layout>
