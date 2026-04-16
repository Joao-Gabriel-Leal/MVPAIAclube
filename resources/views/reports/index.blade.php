<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-4xl">
                <div class="section-title">Analitico gerencial</div>
                <h1 class="display-title mt-3">Relatorios com leitura mais executiva</h1>
                <p class="lead-text mt-3">
                    A area de relatorios agora prioriza comparacao visual, filtros claros e graficos reais para acompanhar operacao, base social, reservas e faturamento.
                </p>
            </div>

            <div class="context-card max-w-md">
                <div class="nav-section-label">Escopo da analise</div>
                <div class="mt-1 text-base font-bold text-slate-900">{{ $reportData['selectedBranch']?->name ?? 'Visao consolidada de filiais' }}</div>
                <p class="mt-1 text-sm text-slate-600">
                    {{ auth()->user()->isAdminBranch() ? 'Seu perfil esta fixado na propria filial, sem mistura com outras unidades.' : 'Selecione uma filial para descer ao detalhe ou mantenha a visao consolidada da matriz.' }}
                </p>
            </div>
        </div>
    </x-slot>

    @php($charts = $reportData['charts'])
    @php($chartPrefix = 'reports-main')

    <div class="panel p-6">
        <form method="GET" class="grid gap-4 xl:grid-cols-[1fr_1fr_1fr_1fr_auto]">
            @if (auth()->user()->isAdminMatrix())
                <div>
                    <label class="field-label" for="branch_id">Filial</label>
                    <select class="field-select" id="branch_id" name="branch_id">
                        <option value="">Todas</option>
                        @foreach ($reportData['branches'] as $branch)
                            <option value="{{ $branch->id }}" @selected(($reportData['filters']['branch_id'] ?? null) == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="branch_id" value="{{ $reportData['filters']['branch_id'] }}">
                <div>
                    <label class="field-label">Filial</label>
                    <div class="field-input flex items-center">{{ $reportData['selectedBranch']?->name }}</div>
                </div>
            @endif

            <div>
                <label class="field-label" for="start_date">Inicio</label>
                <input class="field-input" id="start_date" type="date" name="start_date" value="{{ $reportData['filters']['start_date'] }}" />
            </div>
            <div>
                <label class="field-label" for="end_date">Fim</label>
                <input class="field-input" id="end_date" type="date" name="end_date" value="{{ $reportData['filters']['end_date'] }}" />
            </div>
            <div>
                <label class="field-label" for="status">Status</label>
                <select class="field-select" id="status" name="status">
                    <option value="">Todos</option>
                    @foreach (\App\Enums\MembershipStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(($reportData['filters']['status'] ?? null) === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button class="btn-secondary w-full" type="submit">Atualizar relatorio</button>
            </div>
        </form>
    </div>

    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="stat-card">
            <div class="section-title">Associados</div>
            <div class="mt-3 text-4xl font-bold text-slate-950">{{ $reportData['summary']['members'] }}</div>
            <p class="mt-2 text-sm text-slate-600">Titulares dentro do recorte selecionado.</p>
        </div>
        <div class="stat-card">
            <div class="section-title">Dependentes</div>
            <div class="mt-3 text-4xl font-bold text-slate-950">{{ $reportData['summary']['dependents'] }}</div>
            <p class="mt-2 text-sm text-slate-600">Leitura consolidada da extensao da base.</p>
        </div>
        <div class="stat-card">
            <div class="section-title">Reservas</div>
            <div class="mt-3 text-4xl font-bold text-slate-950">{{ $reportData['summary']['reservations'] }}</div>
            <p class="mt-2 text-sm text-slate-600">Movimento operacional dentro do periodo.</p>
        </div>
        <div class="stat-card">
            <div class="section-title">Receita prevista</div>
            <div class="mt-3 text-4xl font-bold text-slate-950">R$ {{ number_format($reportData['summary']['revenue'], 2, ',', '.') }}</div>
            <p class="mt-2 text-sm text-slate-600">Somatorio das mensalidades geradas.</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1.08fr_0.92fr]">
        <section class="panel p-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="section-title">Comparativo</div>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-950">Associados por filial</h2>
                </div>
                <div class="chip-brand">{{ $reportData['branches']->count() }} filial(is)</div>
            </div>
            <div class="chart-shell mt-6">
                <canvas data-chart-source="{{ $chartPrefix }}-members-branch"></canvas>
            </div>
            <script id="{{ $chartPrefix }}-members-branch" type="application/json">@json($charts['membersByBranch'])</script>
        </section>

        <section class="panel p-6">
            <div class="section-title">Leitura rapida</div>
            <h2 class="mt-2 text-2xl font-semibold text-slate-950">Pontos que ajudam a decidir mais rapido</h2>
            <div class="insight-list mt-6">
                <div class="insight-row">
                    <div>
                        <strong>Ticket medio</strong>
                        <div class="mt-1 text-slate-500">Valor medio das cobrancas no recorte atual.</div>
                    </div>
                    <div class="text-right font-bold text-slate-950">R$ {{ number_format($reportData['summary']['averageTicket'], 2, ',', '.') }}</div>
                </div>
                <div class="insight-row">
                    <div>
                        <strong>Recursos monitorados</strong>
                        <div class="mt-1 text-slate-500">Quantidade de recursos considerados na leitura.</div>
                    </div>
                    <div class="text-right font-bold text-slate-950">{{ $reportData['summary']['resources'] }}</div>
                </div>
                <div class="insight-row">
                    <div>
                        <strong>Maior concentracao de dependentes</strong>
                        <div class="mt-1 text-slate-500">Titular com maior volume de dependentes no recorte.</div>
                    </div>
                    <div class="text-right font-bold text-slate-950">{{ $reportData['dependentsByHolder']->keys()->first() ?? 'Sem destaque' }}</div>
                </div>
                <div class="insight-row">
                    <div>
                        <strong>Plano com mais dependentes</strong>
                        <div class="mt-1 text-slate-500">Ajuda a enxergar pressao por plano e beneficios.</div>
                    </div>
                    <div class="text-right font-bold text-slate-950">{{ $reportData['dependentsByPlan']->keys()->first() ?? 'Sem destaque' }}</div>
                </div>
            </div>
        </section>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <section class="panel p-6">
            <div class="section-title">Base social</div>
            <h2 class="mt-2 text-2xl font-semibold text-slate-950">Associados por status</h2>
            <div class="chart-shell chart-shell-compact mt-6">
                <canvas data-chart-source="{{ $chartPrefix }}-members-status"></canvas>
            </div>
            <script id="{{ $chartPrefix }}-members-status" type="application/json">@json($charts['membersByStatus'])</script>
        </section>

        <section class="panel p-6">
            <div class="section-title">Financeiro</div>
            <h2 class="mt-2 text-2xl font-semibold text-slate-950">Mensalidades por status</h2>
            <div class="chart-shell chart-shell-compact mt-6">
                <canvas data-chart-source="{{ $chartPrefix }}-invoices-status"></canvas>
            </div>
            <script id="{{ $chartPrefix }}-invoices-status" type="application/json">@json($charts['invoicesByStatus'])</script>
        </section>

        <section class="panel p-6">
            <div class="section-title">Operacao</div>
            <h2 class="mt-2 text-2xl font-semibold text-slate-950">Uso de recursos</h2>
            <div class="chart-shell mt-6">
                <canvas data-chart-source="{{ $chartPrefix }}-resource-usage"></canvas>
            </div>
            <script id="{{ $chartPrefix }}-resource-usage" type="application/json">@json($charts['resourceUsage'])</script>
        </section>

        <section class="panel p-6">
            <div class="section-title">Tendencia</div>
            <h2 class="mt-2 text-2xl font-semibold text-slate-950">Reservas por periodo</h2>
            <div class="chart-shell mt-6">
                <canvas data-chart-source="{{ $chartPrefix }}-reservation-trend"></canvas>
            </div>
            <script id="{{ $chartPrefix }}-reservation-trend" type="application/json">@json($charts['reservationTrend'])</script>
        </section>

        <section class="panel p-6">
            <div class="section-title">Comparativo</div>
            <h2 class="mt-2 text-2xl font-semibold text-slate-950">Reservas por filial</h2>
            <div class="chart-shell mt-6">
                <canvas data-chart-source="{{ $chartPrefix }}-reservations-branch"></canvas>
            </div>
            <script id="{{ $chartPrefix }}-reservations-branch" type="application/json">@json($charts['reservationsByBranch'])</script>
        </section>

        <section class="panel p-6">
            <div class="section-title">Detalhamento</div>
            <h2 class="mt-2 text-2xl font-semibold text-slate-950">Titulares e planos com maior concentracao</h2>
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="panel-muted p-5">
                    <div class="font-semibold text-slate-900">Titulares com mais dependentes</div>
                    <div class="mt-4 space-y-3">
                        @forelse ($reportData['dependentsByHolder'] as $holderName => $dependents)
                            <div class="flex items-center justify-between gap-3 rounded-2xl border border-white/70 bg-white/80 px-4 py-3 text-sm">
                                <span class="font-semibold text-slate-900">{{ $holderName }}</span>
                                <span class="chip-brand">{{ $dependents }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Sem titulares com dependentes no recorte.</p>
                        @endforelse
                    </div>
                </div>

                <div class="panel-muted p-5">
                    <div class="font-semibold text-slate-900">Dependentes por plano</div>
                    <div class="mt-4 space-y-3">
                        @forelse ($reportData['dependentsByPlan'] as $planName => $dependents)
                            <div class="flex items-center justify-between gap-3 rounded-2xl border border-white/70 bg-white/80 px-4 py-3 text-sm">
                                <span class="font-semibold text-slate-900">{{ $planName }}</span>
                                <span class="chip-info">{{ $dependents }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Sem dependentes associados a planos no recorte.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
