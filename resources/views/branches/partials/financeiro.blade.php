<div class="space-y-6">
    <section class="panel p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="section-title">Financeiro da filial</div>
                <h2 class="mt-2 text-2xl font-semibold text-slate-950">Resumo executivo da unidade</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">O financeiro local ficou mais direto, com foco em valores por status e historico recente de cobrancas.</p>
            </div>
            <div class="flex w-full max-w-md flex-col gap-3 sm:items-end">
                <form method="GET" action="{{ route('filiais.show', $branch) }}" class="grid w-full gap-3 sm:grid-cols-[minmax(0,1fr)_auto]">
                    <input type="hidden" name="tab" value="financeiro">
                    <input class="field-input" type="month" name="billing_period" value="{{ $financeFilters['billing_period'] }}">
                    <button class="btn-secondary" type="submit">Atualizar competencia</button>
                </form>
                <a href="{{ route('finance.index', ['branch_id' => $branch->id, 'billing_period' => $financeFilters['billing_period']]) }}" class="btn-secondary">Abrir financeiro completo</a>
            </div>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($financeSummary as $card)
                <div class="finance-kpi-card">
                    <div class="relative">
                        <div class="section-title">{{ $card['title'] }}</div>
                        <div class="metric-value">
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
    </section>

    <div class="grid gap-6 xl:grid-cols-[0.86fr_1.14fr]">
        <section class="panel p-6">
            <div class="section-title">Gerar mensalidades</div>
            <h2 class="mt-2 text-2xl font-semibold text-slate-950">Nova competencia</h2>
            <p class="mt-3 text-sm leading-6 text-slate-600">A geracao acontece dentro do contexto da filial, sem misturar dados de outras unidades.</p>

            <form method="POST" action="{{ route('finance.generate') }}" class="mt-6 space-y-4">
                @csrf
                <input type="hidden" name="branch_id" value="{{ $branch->id }}">

                <div>
                    <label class="field-label" for="billing_period">Competencia</label>
                    <input class="field-input" type="month" id="billing_period" name="billing_period" value="{{ old('billing_period', $financeFilters['billing_period']) }}" required />
                </div>

                <div class="rounded-[1.25rem] border border-slate-200 bg-slate-50/85 px-4 py-3 text-sm leading-6 text-slate-600">
                    O processamento considera apenas associados vinculados a esta filial.
                </div>

                <button class="btn-primary w-full" type="submit">Gerar cobrancas</button>
            </form>
        </section>

        <section class="panel p-6">
            <div class="section-title">Ultimas mensalidades</div>
            <h2 class="mt-2 text-2xl font-semibold text-slate-950">Fila recente da filial</h2>

            <div class="mt-5 space-y-4">
                @forelse ($invoices as $invoice)
                    @php($statusClass = match ($invoice->status) {
                        \App\Enums\InvoiceStatus::Paid => 'finance-status-pill--paid',
                        \App\Enums\InvoiceStatus::Overdue => 'finance-status-pill--overdue',
                        default => 'finance-status-pill--pending',
                    })

                    <article class="finance-history-card">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <div class="flex flex-wrap gap-2">
                                    <span class="{{ $statusClass }}">{{ $invoice->status->label() }}</span>
                                </div>
                                <div class="mt-3 font-semibold text-slate-900">{{ $invoice->member->user->name }}</div>
                                <div class="mt-2 text-sm text-slate-600">Competencia {{ $invoice->billing_period->format('m/Y') }} | vencimento {{ $invoice->due_date->format('d/m/Y') }}</div>
                            </div>

                            <div class="text-left lg:text-right">
                                <div class="text-xl font-semibold text-slate-950">R$ {{ number_format((float) $invoice->amount, 2, ',', '.') }}</div>
                            </div>
                        </div>

                        @can('markPaid', $invoice)
                            @if ($invoice->status !== \App\Enums\InvoiceStatus::Paid)
                                <form method="POST" action="{{ route('finance.mark-paid', $invoice) }}" class="mt-4 grid gap-3 md:grid-cols-[1fr_1fr_auto]">
                                    @csrf
                                    <input class="field-input" name="paid_amount" value="{{ $invoice->amount }}" />
                                    <input class="field-input" name="notes" placeholder="Observacao da baixa" />
                                    <button class="btn-secondary" type="submit">Baixar</button>
                                </form>
                            @endif
                        @endcan
                    </article>
                @empty
                    <div class="empty-state">Nenhuma mensalidade encontrada para a competencia atual da filial.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>
