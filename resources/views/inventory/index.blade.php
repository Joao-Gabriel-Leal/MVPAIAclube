<x-app-layout>
    @php($groupedItems = $items->getCollection()->groupBy(fn ($item) => $item->category ?: 'Sem categoria'))

    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-4xl">
                <div class="section-title">Estoque</div>
                <h1 class="display-title mt-3">Operacao da filial</h1>
                <p class="lead-text mt-3">
                    O estoque agora fica mais facil de cadastrar, revisar e movimentar, com itens separados por categoria e acoes concentradas no lugar certo.
                </p>
            </div>
        </div>
    </x-slot>

    @if ($errors->any())
        <div class="mb-6 rounded-3xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900">
            <div class="font-semibold">Nao foi possivel concluir uma das acoes.</div>
            <ul class="mt-2 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="stat-card">
            <div class="section-title">Itens</div>
            <div class="metric-value">{{ $summary['items'] }}</div>
            <p class="mt-2 text-sm text-slate-600">Cadastro atual no recorte informado.</p>
        </div>
        <div class="stat-card">
            <div class="section-title">Em alerta</div>
            <div class="metric-value">{{ $summary['lowStock'] }}</div>
            <p class="mt-2 text-sm text-slate-600">Itens abaixo do minimo configurado.</p>
        </div>
        <div class="stat-card">
            <div class="section-title">Movimentacoes</div>
            <div class="metric-value">{{ $summary['movements'] }}</div>
            <p class="mt-2 text-sm text-slate-600">Entradas, saidas e ajustes registrados.</p>
        </div>
        <div class="stat-card">
            <div class="section-title">Ligadas a reservas</div>
            <div class="metric-value">{{ $summary['reservationLinked'] }}</div>
            <p class="mt-2 text-sm text-slate-600">Consumos ou baixas associados a reservas.</p>
        </div>
    </div>

    <section class="panel mt-6 p-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <div class="section-title">Filtro</div>
                <h2 class="mt-2 text-2xl font-semibold text-slate-950">Recorte atual</h2>
            </div>

            <form method="GET" class="grid gap-3 md:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)_auto_auto]">
                @if (auth()->user()->isAdminMatrix())
                    <select class="field-select" name="branch_id">
                        <option value="">Todas as filiais</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? null) == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                @else
                    <input type="hidden" name="branch_id" value="{{ $filters['branch_id'] }}">
                    <div class="field-input flex items-center">{{ $branches->first()?->name ?? 'Filial atual' }}</div>
                @endif

                <select class="field-select" name="category">
                    <option value="">Todas as categorias</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" @selected(($filters['category'] ?? null) === $category)>{{ $category }}</option>
                    @endforeach
                </select>

                <label class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white/80 px-4 py-3 text-sm font-medium text-slate-700">
                    <input type="checkbox" name="low_stock_only" value="1" class="rounded border-slate-300 text-slate-900 focus:ring-slate-300" @checked($filters['low_stock_only'])>
                    So alertas
                </label>

                <button class="btn-secondary" type="submit">Aplicar</button>
            </form>
        </div>

        <div class="context-card mt-4 max-w-2xl">
            <div class="nav-section-label">Leitura da tela</div>
            <div class="mt-1 text-base font-bold text-slate-900">{{ $filters['category'] ?: 'Visao geral do estoque' }}</div>
            <p class="mt-1 text-sm text-slate-600">
                {{ $filters['low_stock_only'] ? 'O recorte esta mostrando apenas itens em alerta para acelerar a acao operacional.' : 'Os itens abaixo aparecem agrupados por categoria, com formularios recolhidos para reduzir ruido visual.' }}
            </p>
        </div>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
        <div class="panel p-6">
            <div class="section-title">Cadastro</div>
            <h2 class="mt-2 text-2xl font-semibold text-slate-950">Novo item de estoque</h2>
            <p class="mt-3 text-sm leading-6 text-slate-600">
                Cadastre itens operacionais com o minimo necessario para comecar bem: categoria, unidade, saldo inicial e o recurso atendido.
            </p>

            <form method="POST" action="{{ route('inventory.items.store') }}" class="mt-6 space-y-5">
                @csrf
                @if ($filters['category'])
                    <input type="hidden" name="category" value="{{ $filters['category'] }}">
                @endif
                @if ($filters['low_stock_only'])
                    <input type="hidden" name="low_stock_only" value="1">
                @endif

                @if (auth()->user()->isAdminMatrix())
                    <div>
                        <label class="field-label" for="new_branch_id">Filial</label>
                        <select class="field-select" id="new_branch_id" name="branch_id" required>
                            <option value="">Selecione</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" @selected(old('branch_id', $filters['branch_id']) == $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <input type="hidden" name="branch_id" value="{{ $filters['branch_id'] }}">
                    <div>
                        <label class="field-label">Filial</label>
                        <div class="field-input flex items-center">{{ $branches->first()?->name ?? 'Filial atual' }}</div>
                    </div>
                @endif

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="field-label" for="new_name">Nome</label>
                        <input class="field-input" id="new_name" name="name" value="{{ old('name') }}" placeholder="Ex.: Carvao premium" required>
                    </div>
                    <div>
                        <label class="field-label" for="new_category">Categoria</label>
                        <input class="field-input" id="new_category" name="category" value="{{ old('category') }}" placeholder="Limpeza, apoio, alimentos..." required>
                    </div>
                    <div>
                        <label class="field-label" for="new_unit">Unidade</label>
                        <input class="field-input" id="new_unit" name="unit" value="{{ old('unit', 'un') }}" required>
                    </div>
                    <div>
                        <label class="field-label" for="new_current_quantity">Saldo inicial</label>
                        <input class="field-input" id="new_current_quantity" name="current_quantity" type="number" min="0" step="0.01" value="{{ old('current_quantity', 0) }}">
                    </div>
                    <div>
                        <label class="field-label" for="new_minimum_quantity">Estoque minimo</label>
                        <input class="field-input" id="new_minimum_quantity" name="minimum_quantity" type="number" min="0" step="0.01" value="{{ old('minimum_quantity', 0) }}" required>
                    </div>
                    <div>
                        <label class="field-label" for="new_club_resource_id">Recurso vinculado</label>
                        <select class="field-select" id="new_club_resource_id" name="club_resource_id">
                            <option value="">Uso geral da filial</option>
                            @foreach ($resources as $resource)
                                <option value="{{ $resource->id }}" @selected(old('club_resource_id') == $resource->id)>{{ $resource->name }} - {{ $resource->branch?->name ?? 'Filial' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="field-label" for="new_notes">Observacoes</label>
                    <textarea class="field-textarea" id="new_notes" name="notes" placeholder="Ex.: apoio a churrasqueira, manutencao preventiva, uso no salao de eventos">{{ old('notes') }}</textarea>
                </div>

                <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-slate-900 focus:ring-slate-300" @checked(old('is_active', true))>
                    Item ativo para uso
                </label>

                <button class="btn-primary w-full" type="submit">Cadastrar item</button>
            </form>
        </div>

        <div class="panel p-6">
            <div class="section-title">Historico</div>
            <h2 class="mt-2 text-2xl font-semibold text-slate-950">Movimentacoes recentes</h2>
            <div class="mt-6 space-y-3">
                @forelse ($recentMovements as $movement)
                    <article class="panel-muted p-4">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <div class="font-semibold text-slate-900">{{ $movement->item?->name ?? 'Item removido' }}</div>
                                <div class="mt-1 text-sm text-slate-600">{{ $movement->movement_type->label() }} - {{ $movement->reason->label() }}</div>
                                <div class="mt-2 text-xs text-slate-500">{{ $movement->occurred_at?->format('d/m/Y H:i') }} - {{ $movement->branch?->name ?? 'Sem filial' }}</div>
                            </div>

                            <div class="text-left lg:text-right">
                                <div class="chip-info">{{ number_format((float) $movement->quantity, 2, ',', '.') }} {{ $movement->item?->unit ?? '' }}</div>
                                <div class="mt-2 text-xs text-slate-500">{{ $movement->actor?->name ?? 'Sistema' }}</div>
                            </div>
                        </div>

                        <div class="mt-3 text-xs text-slate-500">
                            {{ $movement->resource?->name ?? 'Uso geral da filial' }}
                            @if ($movement->reservation)
                                | Reserva #{{ $movement->reservation->id }} - {{ $movement->reservation->resource?->name ?? 'Sem recurso' }}
                            @endif
                            @if ($movement->notes)
                                | {{ $movement->notes }}
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="empty-state">Nenhuma movimentacao registrada ate agora.</div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="mt-6 space-y-6">
        @forelse ($groupedItems as $category => $categoryItems)
            <section class="panel p-6">
                <div class="inventory-category-header">
                    <div>
                        <div class="section-title">Categoria</div>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-950">{{ $category }}</h2>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="chip-brand">{{ $categoryItems->count() }} item(ns)</span>
                        <span class="chip-warning">{{ $categoryItems->where('is_low_stock', true)->count() }} em alerta</span>
                    </div>
                </div>

                <div class="mt-5 grid gap-4">
                    @foreach ($categoryItems as $item)
                        <article class="inventory-accordion">
                            <div class="px-5 pt-5">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="space-y-3">
                                        <div class="flex flex-wrap gap-2">
                                            <span class="{{ $item->is_active ? 'chip-success' : 'chip-danger' }}">{{ $item->is_active ? 'Ativo' : 'Inativo' }}</span>
                                            @if ($item->is_low_stock)
                                                <span class="chip-warning">Abaixo do minimo</span>
                                            @endif
                                            <span class="chip-info">{{ $item->resource?->name ?? 'Uso geral da filial' }}</span>
                                        </div>

                                        <div>
                                            <h3 class="text-2xl font-semibold text-slate-950">{{ $item->name }}</h3>
                                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ $item->branch?->name ?? 'Sem filial' }} · {{ $item->movements_count }} movimentacao(oes)</p>
                                        </div>
                                    </div>

                                    <div class="grid gap-3 sm:grid-cols-3">
                                        <div class="metric-panel">
                                            <div class="metric-label">Saldo atual</div>
                                            <div class="mt-2 text-xl font-semibold tracking-tight text-slate-950">{{ number_format((float) $item->current_quantity, 2, ',', '.') }} {{ $item->unit }}</div>
                                        </div>
                                        <div class="metric-panel">
                                            <div class="metric-label">Minimo</div>
                                            <div class="mt-2 text-xl font-semibold tracking-tight text-slate-950">{{ number_format((float) $item->minimum_quantity, 2, ',', '.') }} {{ $item->unit }}</div>
                                        </div>
                                        <div class="metric-panel">
                                            <div class="metric-label">Filial</div>
                                            <div class="mt-2 text-sm font-semibold text-slate-950">{{ $item->branch?->name ?? 'Sem filial' }}</div>
                                        </div>
                                    </div>
                                </div>

                                @if ($item->notes)
                                    <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-3 text-sm leading-6 text-slate-600">
                                        {{ $item->notes }}
                                    </div>
                                @endif
                            </div>

                            <details>
                                <summary class="inventory-accordion__summary">
                                    <div>
                                        <div class="section-title">Acoes do item</div>
                                        <div class="mt-2 text-base font-semibold text-slate-950">Abrir ficha e movimentacao</div>
                                    </div>
                                    <span class="btn-secondary">Expandir</span>
                                </summary>

                                <div class="inventory-accordion__body">
                                    <div class="grid gap-4 2xl:grid-cols-2">
                                        <section class="panel-muted p-5">
                                            <div class="section-title">Cadastro</div>
                                            <h4 class="mt-2 text-xl font-semibold text-slate-950">Atualizar ficha</h4>

                                            <form method="POST" action="{{ route('inventory.items.update', $item) }}" class="mt-5 space-y-3">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="branch_id" value="{{ $item->branch_id }}">
                                                @if ($filters['category'])
                                                    <input type="hidden" name="category" value="{{ $filters['category'] }}">
                                                @endif
                                                @if ($filters['low_stock_only'])
                                                    <input type="hidden" name="low_stock_only" value="1">
                                                @endif

                                                <div class="grid gap-3 md:grid-cols-2">
                                                    <input class="field-input" name="name" value="{{ $item->name }}" required>
                                                    <input class="field-input" name="category" value="{{ $item->category }}" required>
                                                    <input class="field-input" name="unit" value="{{ $item->unit }}" required>
                                                    <input class="field-input" name="minimum_quantity" type="number" min="0" step="0.01" value="{{ number_format((float) $item->minimum_quantity, 2, '.', '') }}" required>
                                                </div>

                                                <div>
                                                    <label class="field-label">Recurso vinculado</label>
                                                    <select class="field-select" name="club_resource_id">
                                                        <option value="">Uso geral da filial</option>
                                                        @foreach ($resources->where('branch_id', $item->branch_id) as $resource)
                                                            <option value="{{ $resource->id }}" @selected($item->club_resource_id === $resource->id)>{{ $resource->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div>
                                                    <label class="field-label">Observacoes</label>
                                                    <textarea class="field-textarea" name="notes">{{ $item->notes }}</textarea>
                                                </div>

                                                <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                                                    <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-slate-900 focus:ring-slate-300" @checked($item->is_active)>
                                                    Manter item ativo
                                                </label>

                                                <button class="btn-secondary w-full" type="submit">Salvar ficha</button>
                                            </form>
                                        </section>

                                        <section class="panel-muted p-5">
                                            <div class="section-title">Movimentacao</div>
                                            <h4 class="mt-2 text-xl font-semibold text-slate-950">Entrada, saida ou ajuste</h4>

                                            <form method="POST" action="{{ route('inventory.movements.store', $item) }}" class="mt-5 space-y-3">
                                                @csrf
                                                <input type="hidden" name="branch_id" value="{{ $filters['branch_id'] ?? $item->branch_id }}">
                                                @if ($filters['category'])
                                                    <input type="hidden" name="category" value="{{ $filters['category'] }}">
                                                @endif
                                                @if ($filters['low_stock_only'])
                                                    <input type="hidden" name="low_stock_only" value="1">
                                                @endif

                                                <div class="grid gap-3 md:grid-cols-2">
                                                    <div>
                                                        <label class="field-label">Tipo</label>
                                                        <select class="field-select" name="movement_type">
                                                            @foreach (\App\Enums\InventoryMovementType::cases() as $type)
                                                                <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="field-label">Motivo</label>
                                                        <select class="field-select" name="reason">
                                                            @foreach (\App\Enums\InventoryMovementReason::cases() as $reason)
                                                                <option value="{{ $reason->value }}">{{ $reason->label() }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="field-label">Quantidade</label>
                                                        <input class="field-input" name="quantity" type="number" step="0.01" required>
                                                    </div>
                                                    <div>
                                                        <label class="field-label">Custo unitario</label>
                                                        <input class="field-input" name="unit_cost" type="number" min="0" step="0.01">
                                                    </div>
                                                    <div>
                                                        <label class="field-label">Recurso</label>
                                                        <select class="field-select" name="club_resource_id">
                                                            <option value="">Uso geral da filial</option>
                                                            @foreach ($resources->where('branch_id', $item->branch_id) as $resource)
                                                                <option value="{{ $resource->id }}" @selected($item->club_resource_id === $resource->id)>{{ $resource->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="field-label">Reserva vinculada</label>
                                                        <select class="field-select" name="reservation_id">
                                                            <option value="">Sem reserva</option>
                                                            @foreach ($reservations->where('branch_id', $item->branch_id) as $reservation)
                                                                <option value="{{ $reservation->id }}">
                                                                    #{{ $reservation->id }} - {{ $reservation->resource?->name ?? 'Sem recurso' }} - {{ $reservation->member?->user?->name ?? 'Sem titular' }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div>
                                                    <label class="field-label">Data e hora</label>
                                                    <input class="field-input" name="occurred_at" type="datetime-local" value="{{ now()->format('Y-m-d\TH:i') }}">
                                                </div>

                                                <div>
                                                    <label class="field-label">Observacoes</label>
                                                    <textarea class="field-textarea" name="notes" placeholder="Ex.: consumo em evento, perda operacional, ajuste de contagem"></textarea>
                                                </div>

                                                <button class="btn-primary w-full" type="submit">Registrar movimentacao</button>
                                            </form>
                                        </section>
                                    </div>
                                </div>
                            </details>
                        </article>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="empty-state">Nenhum item encontrado para o recorte atual.</div>
        @endforelse
    </section>

    <div class="mt-6">{{ $items->links() }}</div>
</x-app-layout>
