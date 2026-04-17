<x-app-layout>
    @php($resourceSections = \App\Support\ResourceTypeCatalog::sections($resources))

    <x-slot name="header">
        <div class="max-w-4xl">
            <div class="section-title">Beneficios</div>
            <h1 class="display-title mt-3">Plano e acessos</h1>
            <p class="lead-text mt-3">
                Esta area resume o que o seu plano atual libera dentro do clube, sem criar regras novas fora da configuracao ja existente.
            </p>
        </div>
    </x-slot>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($highlights as $label => $value)
            <div class="stat-card">
                <div class="section-title">{{ $label }}</div>
                <div class="metric-value">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
        <section class="panel p-6">
            <div class="section-title">Configuracao do plano</div>
            <h2 class="mt-2 text-2xl font-semibold text-slate-950">{{ $plan?->name ?? 'Sem plano ativo' }}</h2>
            <p class="mt-3 text-sm leading-6 text-slate-600">{{ $plan?->description ?: 'Nenhuma descricao cadastrada para este plano.' }}</p>

            <div class="mt-6 space-y-3">
                <div class="insight-row">
                    <strong>Mensalidade base</strong>
                    <div class="font-bold text-slate-950">R$ {{ number_format((float) ($plan?->base_price ?? 0), 2, ',', '.') }}</div>
                </div>
                <div class="insight-row">
                    <strong>Desconto extra em reservas</strong>
                    <div class="font-bold text-slate-950">
                        @if (($plan?->extra_reservation_discount_value ?? 0) > 0)
                            {{ number_format((float) $plan->extra_reservation_discount_value, 2, ',', '.') }} {{ $plan->extra_reservation_discount_type?->value === 'percentage' ? '%' : 'reais' }}
                        @else
                            Sem desconto adicional
                        @endif
                    </div>
                </div>
                <div class="insight-row">
                    <strong>Dependentes herdam beneficios</strong>
                    <div class="font-bold text-slate-950">{{ $plan?->dependents_inherit_benefits ? 'Sim' : 'Nao' }}</div>
                </div>
            </div>
        </section>

        <section class="panel p-6">
            <div class="section-title">Recursos permitidos</div>
            <h2 class="mt-2 text-2xl font-semibold text-slate-950">O que este plano libera</h2>
            <p class="mt-3 text-sm leading-6 text-slate-600">Os acessos foram agrupados por tipo para evitar listas extensas e facilitar a consulta no dia a dia.</p>

            <div class="mt-6 space-y-5">
                @forelse ($resourceSections as $section)
                    <section class="rounded-[1.55rem] border border-amber-100/80 bg-amber-50/35 p-5">
                        <div class="flex flex-col gap-3 border-b border-amber-100/80 pb-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="section-title">Tipo</div>
                                <h3 class="mt-2 text-xl font-semibold text-slate-950">{{ $section['label'] }}</h3>
                            </div>
                            <div class="chip-brand">{{ $section['resources']->count() }} acesso(s)</div>
                        </div>

                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            @foreach ($section['resources'] as $resource)
                                <div class="panel-muted p-5">
                                    <div class="font-semibold text-slate-900">{{ $resource->name }}</div>
                                    <div class="mt-2 text-sm text-slate-600">{{ $resource->branch?->name }} - {{ $section['label'] }}</div>
                                    <div class="mt-3 text-xs text-slate-500">
                                        Capacidade {{ $resource->max_capacity }} | Valor base R$ {{ number_format((float) $resource->default_price, 2, ',', '.') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @empty
                    <div class="empty-state">Nenhum recurso especifico vinculado ao plano. O acesso segue a configuracao padrao do clube.</div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
