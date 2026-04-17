<x-app-layout>
    <x-slot name="header">
        <div class="max-w-3xl">
            <div class="section-title">Painel principal</div>
            <h1 class="display-title mt-3">Visao geral do clube</h1>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if ($summary['scope'] === 'member' && ($summary['viewer']?->formatted_card_number))
            <section class="panel p-6 lg:p-8">
                <div class="dashboard-card-spotlight">
                    <div class="dashboard-card-spotlight__intro">
                        <div class="section-title">Carteirinha digital</div>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-950">Seu acesso principal fica aqui</h2>
                        <p class="mt-3 text-sm leading-6 text-slate-600">
                            A carteirinha foi centralizada no inicio do portal para facilitar identificacao, validacao publica e consulta rapida sempre que voce entrar.
                        </p>
                    </div>

                    <div class="w-full max-w-[32rem]">
                        @include('profile.partials.membership-card', ['user' => $summary['viewer'], 'showHeading' => false])
                    </div>
                </div>
            </section>
        @endif

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($summary['cards'] as $label => $value)
                <div class="stat-card">
                    <div class="relative">
                        <div class="section-title">{{ $label }}</div>
                        <div class="metric-value--strong">
                            @if (is_float($value))
                                R$ {{ number_format($value, 2, ',', '.') }}
                            @else
                                {{ $value }}
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </section>

        @if ($summary['scope'] === 'matrix')
            <section class="grid gap-5 lg:grid-cols-[1.08fr_0.92fr]">
                <div class="panel p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="section-title">Comparativo por filial</div>
                            <h2 class="mt-2 text-xl font-semibold text-slate-950">Panorama de unidades</h2>
                        </div>
                        <div class="chip-info">Matriz</div>
                    </div>

                    <div class="table-shell mt-5">
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr>
                                        <th class="table-header-cell">Filial</th>
                                        <th class="table-header-cell">Ativos</th>
                                        <th class="table-header-cell">Recursos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($summary['branchComparisons'] as $branch)
                                        <tr class="table-row">
                                            <td class="table-cell font-bold text-slate-900">{{ $branch->name }}</td>
                                            <td class="table-cell">{{ $branch->active_members_count }}</td>
                                            <td class="table-cell">{{ $branch->resources_count }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="panel p-5">
                    <div class="section-title">Pendencias de aprovacao</div>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">Fila da administracao</h2>

                    <div class="mt-5 space-y-2.5">
                        @forelse (($summary['recentProposals'] ?? collect()) as $proposal)
                            <div class="panel-muted p-3.5">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $proposal['name'] }}</div>
                                        <div class="mt-2 text-sm text-slate-600">{{ $proposal['type_label'] }} - {{ $proposal['branch_name'] }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $proposal['context'] }} - {{ $proposal['origin_label'] }} - {{ $proposal['submitted_age_label'] }}</div>
                                    </div>
                                    <a href="{{ $proposal['show_url'] }}" class="inline-link">Abrir</a>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">Nenhuma proposta pendente no momento.</div>
                        @endforelse
                    </div>
                </div>
            </section>
        @elseif ($summary['scope'] === 'branch')
            <section class="grid gap-5 lg:grid-cols-2">
                <div class="panel p-5">
                    <div class="section-title">Propostas pendentes</div>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">Fila da filial</h2>

                    <div class="mt-5 space-y-2.5">
                        @forelse (($summary['recentProposals'] ?? collect()) as $proposal)
                            <div class="panel-muted p-3.5">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $proposal['name'] }}</div>
                                        <div class="mt-2 text-sm text-slate-600">{{ $proposal['type_label'] }} - {{ $proposal['context'] }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $proposal['origin_label'] }} - {{ $proposal['submitted_age_label'] }}</div>
                                    </div>
                                    <a href="{{ $proposal['show_url'] }}" class="inline-link">Abrir</a>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">Nenhuma proposta pendente na filial.</div>
                        @endforelse
                    </div>
                </div>

                <div class="panel p-5">
                    <div class="section-title">Estoque em alerta</div>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">Itens abaixo do minimo</h2>

                    <div class="mt-5 space-y-2.5">
                        @forelse (($summary['lowStockItems'] ?? collect()) as $item)
                            <div class="panel-muted p-3.5">
                                <div class="font-bold text-slate-900">{{ $item->name }}</div>
                                <div class="mt-2 text-sm text-slate-600">{{ $item->category }} - saldo {{ number_format((float) $item->current_quantity, 2, ',', '.') }} {{ $item->unit }}</div>
                                <div class="mt-1 text-xs text-slate-500">Minimo esperado: {{ number_format((float) $item->minimum_quantity, 2, ',', '.') }} {{ $item->unit }}</div>
                            </div>
                        @empty
                            <div class="empty-state">Nenhum item em alerta no momento.</div>
                        @endforelse
                    </div>
                </div>
            </section>
        @else
            <section class="grid gap-5 lg:grid-cols-[1.15fr_0.85fr]">
                <div class="panel p-5">
                    <div class="section-title">Minhas reservas</div>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">Agenda pessoal</h2>

                    <div class="mt-5 space-y-2.5">
                        @forelse ($summary['recentReservations'] as $reservation)
                            <div class="panel-muted p-3.5">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $reservation->resource->name }}</div>
                                        <div class="mt-2 text-sm text-slate-600">{{ $reservation->reservation_date->format('d/m/Y') }} - {{ $reservation->start_time }} ate {{ $reservation->end_time }}</div>
                                    </div>
                                    <div class="chip-brand">Confirmada</div>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">Nenhuma reserva registrada.</div>
                        @endforelse
                    </div>
                </div>

                <div class="space-y-5">
                    <div class="panel p-5">
                        <div class="section-title">Mensalidades</div>
                        <h2 class="mt-2 text-xl font-semibold text-slate-950">Ultimas cobrancas</h2>

                        <div class="mt-5 space-y-2.5">
                            @forelse ($summary['recentInvoices'] as $invoice)
                                <div class="panel-muted p-3.5">
                                    <div class="font-bold text-slate-900">{{ $invoice->billing_period->format('m/Y') }}</div>
                                    <div class="mt-2 text-sm text-slate-600">R$ {{ number_format((float) $invoice->amount, 2, ',', '.') }} - {{ $invoice->status->label() }}</div>
                                </div>
                            @empty
                                <div class="empty-state">Nenhuma mensalidade encontrada.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="panel p-5">
                        <div class="section-title">Dependentes</div>
                        <h2 class="mt-2 text-xl font-semibold text-slate-950">Vinculos ativos</h2>

                        <div class="mt-5 space-y-2.5">
                            @forelse ($summary['dependents'] as $dependent)
                                <div class="panel-muted p-3.5">
                                    <div class="font-bold text-slate-900">{{ $dependent->user->name }}</div>
                                    <div class="mt-2 text-sm text-slate-600">{{ $dependent->relationship }} - {{ $dependent->status->label() }}</div>
                                </div>
                            @empty
                                <div class="empty-state">Nenhum dependente vinculado.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
