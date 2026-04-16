<x-app-layout>
    <x-slot name="header">
        <div class="max-w-3xl">
            <div class="section-title">Painel principal</div>
            <h1 class="display-title mt-3">Visao geral do clube</h1>
            <p class="lead-text mt-3">
                Acompanhe operacao, reservas e pendencias com uma leitura mais clara e visualmente consistente.
            </p>
        </div>
    </x-slot>

    <div class="space-y-8">
        @if ($summary['scope'] === 'member' && ($summary['viewer']?->formatted_card_number))
            @include('profile.partials.membership-card', ['user' => $summary['viewer']])
        @endif

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($summary['cards'] as $label => $value)
                <div class="stat-card">
                    <div class="relative">
                        <div class="section-title">{{ $label }}</div>
                        <div class="mt-5 text-4xl font-black tracking-tight text-slate-950">
                            @if (is_float($value))
                                R$ {{ number_format($value, 2, ',', '.') }}
                            @else
                                {{ $value }}
                            @endif
                        </div>
                        <p class="mt-3 text-sm leading-6 text-slate-500">Atualizado para a leitura rapida do dia.</p>
                    </div>
                </div>
            @endforeach
        </section>

        @if ($summary['scope'] === 'matrix')
            <section class="grid gap-6 lg:grid-cols-[1.08fr_0.92fr]">
                <div class="panel p-6">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="section-title">Comparativo por filial</div>
                            <h2 class="mt-2 text-2xl font-semibold text-slate-950">Panorama de unidades</h2>
                        </div>
                        <div class="chip-info">Matriz</div>
                    </div>

                    <div class="table-shell mt-6">
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

                <div class="panel p-6">
                    <div class="section-title">Pendencias de aprovacao</div>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-950">Fila da administracao</h2>

                    <div class="mt-6 space-y-3">
                        @forelse ($summary['pendingMembers'] as $member)
                            <div class="panel-muted p-4">
                                <div class="font-bold text-slate-900">{{ $member->user->name }}</div>
                                <div class="mt-2 text-sm text-slate-600">{{ $member->primaryBranch->name }} - {{ $member->plan->name }}</div>
                            </div>
                        @empty
                            <div class="empty-state">Nenhum associado pendente no momento.</div>
                        @endforelse
                    </div>
                </div>
            </section>
        @elseif ($summary['scope'] === 'branch')
            <section class="grid gap-6 lg:grid-cols-2">
                <div class="panel p-6">
                    <div class="section-title">Associados pendentes</div>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-950">Aprovacoes da filial</h2>

                    <div class="mt-6 space-y-3">
                        @forelse ($summary['pendingMembers'] as $member)
                            <div class="panel-muted p-4">
                                <div class="font-bold text-slate-900">{{ $member->user->name }}</div>
                                <div class="mt-2 text-sm text-slate-600">{{ $member->plan->name }}</div>
                            </div>
                        @empty
                            <div class="empty-state">Nenhum associado pendente na filial.</div>
                        @endforelse
                    </div>
                </div>

                <div class="panel p-6">
                    <div class="section-title">Reservas recentes</div>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-950">Agenda em movimento</h2>

                    <div class="mt-6 space-y-3">
                        @forelse ($summary['recentReservations'] as $reservation)
                            <div class="panel-muted p-4">
                                <div class="font-bold text-slate-900">{{ $reservation->resource->name }}</div>
                                <div class="mt-2 text-sm text-slate-600">{{ $reservation->member->user->name }} - {{ $reservation->reservation_date->format('d/m/Y') }}</div>
                            </div>
                        @empty
                            <div class="empty-state">Sem reservas recentes.</div>
                        @endforelse
                    </div>
                </div>
            </section>
        @else
            <section class="grid gap-6 lg:grid-cols-[1.15fr_0.85fr]">
                <div class="panel p-6">
                    <div class="section-title">Minhas reservas</div>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-950">Agenda pessoal</h2>

                    <div class="mt-6 space-y-3">
                        @forelse ($summary['recentReservations'] as $reservation)
                            <div class="panel-muted p-4">
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

                <div class="space-y-6">
                    <div class="panel p-6">
                        <div class="section-title">Mensalidades</div>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-950">Ultimas cobrancas</h2>

                        <div class="mt-6 space-y-3">
                            @forelse ($summary['recentInvoices'] as $invoice)
                                <div class="panel-muted p-4">
                                    <div class="font-bold text-slate-900">{{ $invoice->billing_period->format('m/Y') }}</div>
                                    <div class="mt-2 text-sm text-slate-600">R$ {{ number_format((float) $invoice->amount, 2, ',', '.') }} - {{ $invoice->status->label() }}</div>
                                </div>
                            @empty
                                <div class="empty-state">Nenhuma mensalidade encontrada.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="panel p-6">
                        <div class="section-title">Dependentes</div>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-950">Vinculos ativos</h2>

                        <div class="mt-6 space-y-3">
                            @forelse ($summary['dependents'] as $dependent)
                                <div class="panel-muted p-4">
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
