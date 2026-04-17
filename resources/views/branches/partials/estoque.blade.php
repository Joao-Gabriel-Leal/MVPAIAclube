<div class="space-y-6">
    <section class="panel p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="section-title">Estoque da filial</div>
                <h2 class="mt-2 text-2xl font-semibold text-slate-950">Operacao e alertas</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    Acompanhe insumos de reserva, limpeza, manutencao e outros materiais da unidade sem sair do contexto da filial.
                </p>
            </div>

            <a href="{{ route('inventory.index', ['branch_id' => $branch->id]) }}" class="btn-primary">Abrir estoque completo</a>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <div class="panel p-6">
            <div class="section-title">Itens em alerta</div>
            <div class="mt-4 space-y-3">
                @forelse ($lowStockItems as $item)
                    <div class="panel-muted p-4">
                        <div class="font-semibold text-slate-900">{{ $item->name }}</div>
                        <div class="mt-1 text-sm text-slate-600">{{ $item->category }} - {{ $item->resource?->name ?? 'Uso geral da filial' }}</div>
                        <div class="mt-2 text-xs text-slate-500">Saldo {{ number_format((float) $item->current_quantity, 2, ',', '.') }} {{ $item->unit }} | Minimo {{ number_format((float) $item->minimum_quantity, 2, ',', '.') }} {{ $item->unit }}</div>
                    </div>
                @empty
                    <div class="empty-state">Nenhum item abaixo do minimo nesta filial.</div>
                @endforelse
            </div>
        </div>

        <div class="panel p-6">
            <div class="section-title">Movimentacoes recentes</div>
            <div class="mt-4 space-y-3">
                @forelse ($recentInventoryMovements as $movement)
                    <div class="panel-muted p-4">
                        <div class="font-semibold text-slate-900">{{ $movement->item->name }}</div>
                        <div class="mt-1 text-sm text-slate-600">{{ $movement->movement_type->label() }} - {{ $movement->reason->label() }}</div>
                        <div class="mt-2 text-xs text-slate-500">
                            {{ number_format((float) $movement->quantity, 2, ',', '.') }} {{ $movement->item->unit }}
                            @if ($movement->reservation)
                                - Reserva {{ $movement->reservation->resource?->name ?? '#'.$movement->reservation->id }}
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty-state">Nenhuma movimentacao registrada para esta filial.</div>
                @endforelse
            </div>
        </div>
    </section>
</div>
