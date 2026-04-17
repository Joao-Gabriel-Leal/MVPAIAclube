<x-app-layout>
    <x-slot name="header">
        <div class="max-w-4xl">
            <div class="section-title">Faturas</div>
            <h1 class="display-title mt-3">Minhas mensalidades</h1>
            <p class="lead-text mt-3">
                Consulte as cobrancas do seu cadastro, gere uma segunda via em HTML e abra o comprovante quando a baixa ja estiver registrada.
            </p>
        </div>
    </x-slot>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="stat-card">
            <div class="section-title">Em aberto</div>
            <div class="metric-value">{{ $summary['open'] }}</div>
        </div>
        <div class="stat-card">
            <div class="section-title">Pagas</div>
            <div class="metric-value">{{ $summary['paid'] }}</div>
        </div>
        <div class="stat-card">
            <div class="section-title">Em atraso</div>
            <div class="metric-value">{{ $summary['overdue'] }}</div>
        </div>
    </div>

    <div class="mt-6 grid gap-4">
        @forelse ($invoices as $invoice)
            @php($statusClass = match($invoice->status->value) {
                'paid' => 'chip-success',
                'overdue' => 'chip-danger',
                default => 'chip-warning',
            })

            <div class="panel p-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <div class="section-title">{{ $invoice->branch->name }}</div>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-950">{{ $invoice->billing_period->format('m/Y') }}</h2>
                        <p class="mt-3 text-sm leading-6 text-slate-600">
                            Vencimento em {{ $invoice->due_date->format('d/m/Y') }} | Valor R$ {{ number_format((float) $invoice->amount, 2, ',', '.') }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <span class="{{ $statusClass }}">{{ $invoice->status->label() }}</span>
                        <a href="{{ route('member-invoices.show', $invoice) }}" class="btn-secondary">Segunda via</a>
                        @if ($invoice->status->value === 'paid')
                            <a href="{{ route('member-invoices.receipt', $invoice) }}" class="btn-primary">Comprovante</a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state">Nenhuma mensalidade encontrada para este cadastro.</div>
        @endforelse
    </div>

    <div class="mt-6">{{ $invoices->links() }}</div>
</x-app-layout>
