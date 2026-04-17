<x-app-layout>
    <x-slot name="header">
        <div class="max-w-4xl">
            <div class="section-title">Financeiro</div>
            <h1 class="display-title mt-2">Mensalidades e cobrancas</h1>
            <p class="lead-text mt-3">Acompanhe o faturamento da rede com filtros claros, resumo por status e uma fila padronizada de baixas.</p>
        </div>
    </x-slot>

    <div class="finance-summary-grid">
        @foreach ($summary as $card)
            <div class="finance-kpi-card">
                <div class="relative">
                    <div class="section-title">{{ $card['title'] }}</div>
                    <div class="metric-value--strong">
                        @if ($card['isCurrency'])
                            R$ {{ number_format((float) $card['value'], 2, ',', '.') }}
                        @else
                            {{ $card['value'] }}
                        @endif
                    </div>
                    <p class="mt-2 text-sm leading-5 text-slate-600">{{ $card['context'] }}</p>
                    @if ($card['detail'])
                        <p class="mt-1 text-xs text-slate-500">{{ $card['detail'] }}</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <section class="panel mt-6 p-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <div class="section-title">Recorte</div>
                <h2 class="mt-2 text-2xl font-semibold text-slate-950">Filtros do financeiro</h2>
            </div>

            <form method="GET" class="grid gap-3 md:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)_minmax(0,1fr)_auto]">
                @if (auth()->user()->isAdminMatrix())
                    <select class="field-select" id="branch_id" name="branch_id">
                        <option value="">Todas as filiais</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(($selectedBranch?->id ?? null) === $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                @else
                    <input type="hidden" name="branch_id" value="{{ $selectedBranch?->id }}">
                    <div class="field-input flex items-center">{{ $selectedBranch?->name ?? 'Filial atual' }}</div>
                @endif

                <input class="field-input" id="billing_period" type="month" name="billing_period" value="{{ $filters['billing_period'] }}" />

                <select class="field-select" id="status" name="status">
                    <option value="">Todos os status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected($statusFilter === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>

                <button class="btn-secondary" type="submit">Atualizar visao</button>
            </form>
        </div>

        <div class="context-card mt-4 max-w-2xl">
            <div class="nav-section-label">Contexto financeiro</div>
            <div class="mt-1 text-base font-bold text-slate-900">{{ $selectedBranch?->name ?? 'Consolidado da rede' }}</div>
            <p class="mt-1 text-sm text-slate-600">
                Competencia {{ $billingPeriod->format('m/Y') }} | {{ $selectedBranch ? 'Os indicadores e cobrancas abaixo refletem apenas a filial selecionada.' : 'Sem filtro de filial, voce esta vendo o consolidado permitido pelo seu perfil.' }}
            </p>
        </div>
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-[0.88fr_1.12fr]">
        <section class="panel p-6">
            <div class="section-title">Gerar mensalidades</div>
            <h2 class="mt-2 text-2xl font-semibold text-slate-950">Abrir nova competencia</h2>
            <p class="mt-3 text-sm leading-6 text-slate-600">Use este bloco para gerar cobrancas em lote dentro do contexto atual da operacao.</p>

            <form method="POST" action="{{ route('finance.generate') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label class="field-label" for="generate_billing_period">Competencia</label>
                    <input class="field-input" type="month" id="generate_billing_period" name="billing_period" value="{{ old('billing_period', $filters['billing_period']) }}" required />
                </div>

                @if (auth()->user()->isAdminMatrix())
                    <div>
                        <label class="field-label" for="generate_branch_id">Filial</label>
                        <select class="field-select" id="generate_branch_id" name="branch_id">
                            <option value="">Todas as filiais</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" @selected((string) old('branch_id', $selectedBranch?->id) === (string) $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <input type="hidden" name="branch_id" value="{{ $selectedBranch?->id }}">
                    <div>
                        <label class="field-label">Filial</label>
                        <div class="field-input flex items-center">{{ $selectedBranch?->name ?? 'Filial atual' }}</div>
                    </div>
                @endif

                <div class="rounded-[1.25rem] border border-slate-200 bg-slate-50/85 px-4 py-3 text-sm leading-6 text-slate-600">
                    A geracao respeita o contexto da filial informada e atualiza os indicadores desta tela logo depois da execucao.
                </div>

                <button class="btn-primary w-full" type="submit">Gerar cobrancas</button>
            </form>
        </section>

        <section class="panel p-6">
            <div class="flex flex-col gap-3 border-b border-slate-200/80 pb-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="section-title">Historico</div>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-950">Cobrancas registradas</h2>
                </div>
                <div class="chip-brand">{{ $invoices->total() }} registro(s)</div>
            </div>

            <div class="mt-6 space-y-4">
                @forelse ($invoices as $invoice)
                    @php($statusClass = match ($invoice->status) {
                        \App\Enums\InvoiceStatus::Paid => 'finance-status-pill--paid',
                        \App\Enums\InvoiceStatus::Overdue => 'finance-status-pill--overdue',
                        default => 'finance-status-pill--pending',
                    })

                    <article class="finance-history-card">
                        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                            <div class="space-y-3">
                                <div class="flex flex-wrap gap-2">
                                    <span class="{{ $statusClass }}">{{ $invoice->status->label() }}</span>
                                    <span class="chip-info">{{ $invoice->branch->name }}</span>
                                </div>

                                <div>
                                    <h3 class="text-xl font-semibold text-slate-950">{{ $invoice->member->user->name }}</h3>
                                    <p class="mt-2 text-sm leading-6 text-slate-600">
                                        Competencia {{ $invoice->billing_period->format('m/Y') }} | vencimento {{ $invoice->due_date->format('d/m/Y') }}
                                    </p>
                                </div>
                            </div>

                            <div class="text-left xl:text-right">
                                <div class="text-2xl font-semibold text-slate-950">R$ {{ number_format((float) $invoice->amount, 2, ',', '.') }}</div>
                                <div class="mt-2 text-sm text-slate-500">Atualizado em {{ $invoice->updated_at?->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>

                        @can('markPaid', $invoice)
                            @if ($invoice->status !== \App\Enums\InvoiceStatus::Paid)
                                <form method="POST" action="{{ route('finance.mark-paid', $invoice) }}" class="mt-5 grid gap-3 lg:grid-cols-[1fr_1fr_auto]">
                                    @csrf
                                    <input class="field-input" name="paid_amount" value="{{ $invoice->amount }}" />
                                    <input class="field-input" name="notes" placeholder="Observacao da baixa" />
                                    <button class="btn-secondary" type="submit">Baixar</button>
                                </form>
                            @endif
                        @endcan
                    </article>
                @empty
                    <div class="empty-state">Nenhuma mensalidade encontrada para a competencia e filtros atuais.</div>
                @endforelse
            </div>

            <div class="mt-6">{{ $invoices->links() }}</div>
        </section>
    </div>
</x-app-layout>
