<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-4xl">
                <div class="section-title">Propostas</div>
                <h1 class="display-title mt-3">Fila de analise</h1>
                <p class="lead-text mt-3">
                    Acompanhe associados e dependentes pendentes em uma fila unica, com filtros por filial, origem e idade da solicitacao.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        <div class="stat-card">
            <div class="section-title">Total</div>
            <div class="metric-value">{{ $summary['total'] }}</div>
        </div>
        <div class="stat-card">
            <div class="section-title">Associados</div>
            <div class="metric-value">{{ $summary['members'] }}</div>
        </div>
        <div class="stat-card">
            <div class="section-title">Dependentes</div>
            <div class="metric-value">{{ $summary['dependents'] }}</div>
        </div>
        <div class="stat-card">
            <div class="section-title">Adesao publica</div>
            <div class="metric-value">{{ $summary['public'] }}</div>
        </div>
        <div class="stat-card">
            <div class="section-title">Em atencao</div>
            <div class="metric-value">{{ $summary['attention'] }}</div>
        </div>
    </div>

    <div class="panel mt-6 p-6">
        <form method="GET" class="grid gap-4 xl:grid-cols-[1fr_1fr_1fr_1fr_auto]">
            @if (auth()->user()->isAdminMatrix())
                <div>
                    <label class="field-label" for="branch_id">Filial</label>
                    <select class="field-select" id="branch_id" name="branch_id">
                        <option value="">Todas</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? null) == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="branch_id" value="{{ $filters['branch_id'] }}">
                <div>
                    <label class="field-label">Filial</label>
                    <div class="field-input flex items-center">{{ $branches->first()?->name }}</div>
                </div>
            @endif

            <div>
                <label class="field-label" for="type">Tipo</label>
                <select class="field-select" id="type" name="type">
                    <option value="">Todos</option>
                    <option value="member" @selected(($filters['type'] ?? null) === 'member')>Associado</option>
                    <option value="dependent" @selected(($filters['type'] ?? null) === 'dependent')>Dependente</option>
                </select>
            </div>

            <div>
                <label class="field-label" for="origin">Origem</label>
                <select class="field-select" id="origin" name="origin">
                    <option value="">Todas</option>
                    <option value="manual" @selected(($filters['origin'] ?? null) === 'manual')>Manual</option>
                    <option value="public" @selected(($filters['origin'] ?? null) === 'public')>Adesao publica</option>
                </select>
            </div>

            <div>
                <label class="field-label" for="age">Idade da solicitacao</label>
                <select class="field-select" id="age" name="age">
                    <option value="">Todas</option>
                    <option value="recent" @selected(($filters['age'] ?? null) === 'recent')>Ate 3 dias</option>
                    <option value="attention" @selected(($filters['age'] ?? null) === 'attention')>4 a 7 dias</option>
                    <option value="stale" @selected(($filters['age'] ?? null) === 'stale')>Mais de 7 dias</option>
                </select>
            </div>

            <div class="flex items-end">
                <button class="btn-secondary w-full" type="submit">Filtrar</button>
            </div>
        </form>
    </div>

    <div class="mt-6 grid gap-4">
        @forelse ($proposals as $proposal)
            <div class="panel p-6">
                <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                    <div class="space-y-3">
                        <div class="flex flex-wrap gap-2">
                            <span class="chip-brand">{{ $proposal['type_label'] }}</span>
                            <span class="chip-info">{{ $proposal['origin_label'] }}</span>
                            <span class="chip-age">{{ $proposal['submitted_age_label'] }}</span>
                        </div>

                        <div>
                            <h2 class="text-2xl font-semibold text-slate-950">{{ $proposal['name'] }}</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                {{ $proposal['email'] }} - {{ $proposal['branch_name'] }} - {{ $proposal['context'] }}
                            </p>
                        </div>
                    </div>

                    <a href="{{ $proposal['show_url'] }}" class="btn-secondary">Abrir ficha</a>
                </div>

                <form method="POST" action="{{ $proposal['status_url'] }}" class="mt-5 grid gap-3 xl:grid-cols-[1fr_auto_auto]">
                    @csrf
                    <textarea class="field-textarea" name="notes" placeholder="Observacao da analise (opcional)"></textarea>
                    <button class="btn-primary" type="submit" name="status" value="active">Aprovar</button>
                    <button class="btn-secondary" type="submit" name="status" value="cancelled">Reprovar</button>
                </form>
            </div>
        @empty
            <div class="empty-state">Nenhuma proposta encontrada com os filtros atuais.</div>
        @endforelse
    </div>

    <div class="mt-6">{{ $proposals->links() }}</div>
</x-app-layout>
