<div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
    <section class="panel p-6">
        <div class="section-title">Gerar mensalidades</div>
        <h2 class="mt-2 text-2xl font-semibold text-slate-950">Financeiro da filial</h2>
        <p class="mt-3 text-sm leading-6 text-slate-600">
            O faturamento passa a ser tratado dentro do contexto da unidade, evitando a sensacao de dados misturados.
        </p>

        <form method="POST" action="{{ route('finance.generate') }}" class="mt-6 space-y-4">
            @csrf
            <input type="hidden" name="branch_id" value="{{ $branch->id }}">

            <div>
                <label class="field-label" for="billing_period">Competencia</label>
                <input class="field-input" type="month" id="billing_period" name="billing_period" value="{{ old('billing_period', now()->format('Y-m')) }}" required />
            </div>

            <button class="btn-primary w-full" type="submit">Gerar cobrancas</button>
        </form>
    </section>

    <section class="panel p-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <div class="section-title">Ultimas mensalidades</div>
                <h2 class="mt-2 text-2xl font-semibold text-slate-950">Historico recente da filial</h2>
            </div>
            <a href="{{ route('finance.index', ['branch_id' => $branch->id]) }}" class="btn-secondary">Abrir financeiro completo</a>
        </div>

        <div class="mt-5 space-y-4">
            @forelse ($invoices as $invoice)
                <article class="rounded-2xl border border-slate-100 p-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <div class="font-semibold text-slate-900">{{ $invoice->member->user->name }}</div>
                            <div class="mt-1 text-sm text-slate-600">{{ $invoice->billing_period->format('m/Y') }} - {{ $invoice->status->label() }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold text-slate-900">R$ {{ number_format((float) $invoice->amount, 2, ',', '.') }}</div>
                            <div class="text-sm text-slate-600">Vencimento {{ $invoice->due_date->format('d/m/Y') }}</div>
                        </div>
                    </div>

                    @can('markPaid', $invoice)
                        @if ($invoice->status !== \App\Enums\InvoiceStatus::Paid)
                            <form method="POST" action="{{ route('finance.mark-paid', $invoice) }}" class="mt-4 grid gap-3 md:grid-cols-[1fr_1fr_auto]">
                                @csrf
                                <input class="field-input" name="paid_amount" value="{{ $invoice->amount }}" />
                                <input class="field-input" name="notes" placeholder="Observacao da baixa">
                                <button class="btn-secondary" type="submit">Baixar</button>
                            </form>
                        @endif
                    @endcan
                </article>
            @empty
                <div class="empty-state">Nenhuma mensalidade encontrada para esta filial.</div>
            @endforelse
        </div>
    </section>
</div>
