@php($prefix = 'branch-'.$branch->id)
@php($charts = $reportData['charts'])

<div class="space-y-6">
    <section class="panel p-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <div class="section-title">Relatorios da filial</div>
                <h2 class="mt-2 text-2xl font-semibold text-slate-950">Graficos e leitura gerencial desta unidade</h2>
                <p class="mt-2 text-sm text-slate-600">
                    Os filtros abaixo refinam a leitura sem tirar a operacao do contexto da propria filial.
                </p>
            </div>

            <form method="GET" action="{{ route('filiais.show', $branch) }}" class="grid gap-3 md:grid-cols-4">
                <input type="hidden" name="tab" value="relatorios">
                <input class="field-input" type="date" name="start_date" value="{{ $filters['start_date'] }}">
                <input class="field-input" type="date" name="end_date" value="{{ $filters['end_date'] }}">
                <select class="field-select" name="status">
                    <option value="">Todos os status</option>
                    @foreach (\App\Enums\MembershipStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? null) === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
                <button class="btn-secondary" type="submit">Atualizar</button>
            </form>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.08fr_0.92fr]">
        <div class="panel p-6">
            <div class="section-title">Base social</div>
            <h2 class="mt-2 text-2xl font-semibold text-slate-950">Associados por status</h2>
            <div class="chart-shell mt-6">
                <canvas data-chart-source="{{ $prefix }}-members-status"></canvas>
            </div>
            <script id="{{ $prefix }}-members-status" type="application/json">@json($charts['membersByStatus'])</script>
        </div>

        <div class="panel p-6">
            <div class="section-title">Leitura rapida</div>
            <h2 class="mt-2 text-2xl font-semibold text-slate-950">Sinais de operacao da filial</h2>
            <div class="insight-list mt-6">
                <div class="insight-row">
                    <div>
                        <strong>Receita prevista</strong>
                        <div class="mt-1 text-slate-500">Mensalidades acumuladas no recorte atual.</div>
                    </div>
                    <div class="font-bold text-slate-950">R$ {{ number_format($reportData['summary']['revenue'], 2, ',', '.') }}</div>
                </div>
                <div class="insight-row">
                    <div>
                        <strong>Reservas no periodo</strong>
                        <div class="mt-1 text-slate-500">Volume total de uso da filial no intervalo filtrado.</div>
                    </div>
                    <div class="font-bold text-slate-950">{{ $reportData['summary']['reservations'] }}</div>
                </div>
                <div class="insight-row">
                    <div>
                        <strong>Ticket medio</strong>
                        <div class="mt-1 text-slate-500">Valor medio das cobrancas da unidade.</div>
                    </div>
                    <div class="font-bold text-slate-950">R$ {{ number_format($reportData['summary']['averageTicket'], 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <div class="panel p-6">
            <div class="section-title">Financeiro</div>
            <h2 class="mt-2 text-2xl font-semibold text-slate-950">Mensalidades por status</h2>
            <div class="chart-shell chart-shell-compact mt-6">
                <canvas data-chart-source="{{ $prefix }}-invoices-status"></canvas>
            </div>
            <script id="{{ $prefix }}-invoices-status" type="application/json">@json($charts['invoicesByStatus'])</script>
        </div>

        <div class="panel p-6">
            <div class="section-title">Operacao</div>
            <h2 class="mt-2 text-2xl font-semibold text-slate-950">Uso de recursos</h2>
            <div class="chart-shell mt-6">
                <canvas data-chart-source="{{ $prefix }}-resources"></canvas>
            </div>
            <script id="{{ $prefix }}-resources" type="application/json">@json($charts['resourceUsage'])</script>
        </div>

        <div class="panel p-6">
            <div class="section-title">Tendencia</div>
            <h2 class="mt-2 text-2xl font-semibold text-slate-950">Reservas por periodo</h2>
            <div class="chart-shell mt-6">
                <canvas data-chart-source="{{ $prefix }}-trend"></canvas>
            </div>
            <script id="{{ $prefix }}-trend" type="application/json">@json($charts['reservationTrend'])</script>
        </div>

        <div class="panel p-6">
            <div class="section-title">Detalhamento</div>
            <h2 class="mt-2 text-2xl font-semibold text-slate-950">Planos e titulares com maior concentracao</h2>
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="panel-muted p-5">
                    <div class="font-semibold text-slate-900">Dependentes por plano</div>
                    <div class="mt-4 space-y-3">
                        @forelse ($reportData['dependentsByPlan'] as $planName => $dependents)
                            <div class="flex items-center justify-between gap-3 rounded-2xl border border-white/70 bg-white/80 px-4 py-3 text-sm">
                                <span class="font-semibold text-slate-900">{{ $planName }}</span>
                                <span class="chip-info">{{ $dependents }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Sem dependentes vinculados a planos no recorte.</p>
                        @endforelse
                    </div>
                </div>

                <div class="panel-muted p-5">
                    <div class="font-semibold text-slate-900">Titulares com mais dependentes</div>
                    <div class="mt-4 space-y-3">
                        @forelse ($reportData['dependentsByHolder'] as $holderName => $dependents)
                            <div class="flex items-center justify-between gap-3 rounded-2xl border border-white/70 bg-white/80 px-4 py-3 text-sm">
                                <span class="font-semibold text-slate-900">{{ $holderName }}</span>
                                <span class="chip-brand">{{ $dependents }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Sem titulares destacados no recorte.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
