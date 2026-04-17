<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="section-title">Segunda via</div>
                <h1 class="display-title mt-3">Fatura {{ $invoice->billing_period->format('m/Y') }}</h1>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('member-invoices.index') }}" class="btn-secondary">Voltar</a>
                @if ($invoice->status->value === 'paid')
                    <a href="{{ route('member-invoices.receipt', $invoice) }}" class="btn-secondary">Abrir comprovante</a>
                @endif
                <button type="button" class="btn-primary" onclick="window.print()">Imprimir</button>
            </div>
        </div>
    </x-slot>

    <div class="panel p-6">
        <div class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
            <div>
                <div class="section-title">Titular</div>
                <h2 class="mt-2 text-2xl font-semibold text-slate-950">{{ $invoice->member->user->name }}</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">{{ $invoice->branch->name }}</p>

                <div class="mt-6 space-y-3">
                    <div class="insight-row">
                        <strong>Competencia</strong>
                        <div class="font-bold text-slate-950">{{ $invoice->billing_period->format('m/Y') }}</div>
                    </div>
                    <div class="insight-row">
                        <strong>Vencimento</strong>
                        <div class="font-bold text-slate-950">{{ $invoice->due_date->format('d/m/Y') }}</div>
                    </div>
                    <div class="insight-row">
                        <strong>Status</strong>
                        <div class="font-bold text-slate-950">{{ $invoice->status->label() }}</div>
                    </div>
                    <div class="insight-row">
                        <strong>Valor</strong>
                        <div class="font-bold text-slate-950">R$ {{ number_format((float) $invoice->amount, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            <div class="panel-muted p-5">
                <div class="section-title">Orientacoes</div>
                <h3 class="mt-2 text-xl font-semibold text-slate-950">Pagamento e comprovacao</h3>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    Esta segunda via serve como referencia interna do clube para consulta da mensalidade. Nesta fase do sistema, a baixa continua sendo manual pela administracao.
                </p>

                @if ($invoice->notes)
                    <div class="mt-5 rounded-2xl border border-white/70 bg-white/80 p-4 text-sm leading-6 text-slate-600">
                        {{ $invoice->notes }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
