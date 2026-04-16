<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="section-title">Financeiro</div>
            <h1 class="display-title mt-2">Mensalidades</h1>
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-[0.85fr_1.15fr]">
        <div class="panel p-6">
            <div class="section-title">Gerar mensalidades</div>
            <form method="POST" action="{{ route('finance.generate') }}" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label class="field-label" for="billing_period">Competencia</label>
                    <input class="field-input" type="month" id="billing_period" name="billing_period" value="{{ old('billing_period', now()->format('Y-m')) }}" required />
                </div>
                @if (auth()->user()->isAdminMatrix())
                    <div>
                        <label class="field-label" for="branch_id">Filial</label>
                        <select class="field-select" id="branch_id" name="branch_id">
                            <option value="">Todas</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <button class="btn-primary w-full" type="submit">Gerar cobrancas</button>
            </form>
        </div>

        <div class="panel p-6">
            <div class="section-title">Historico</div>
            <div class="mt-5 space-y-4">
                @foreach ($invoices as $invoice)
                    <div class="rounded-2xl border border-slate-100 p-4">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <div class="font-semibold text-slate-900">{{ $invoice->member->user->name }}</div>
                                <div class="mt-1 text-sm text-slate-600">{{ $invoice->branch->name }} · {{ $invoice->billing_period->format('m/Y') }} · {{ $invoice->status->label() }}</div>
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
                    </div>
                @endforeach
            </div>

            <div class="mt-6">{{ $invoices->links() }}</div>
        </div>
    </div>
</x-app-layout>
