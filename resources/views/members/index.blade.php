<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-4xl">
                <div class="section-title">Base social</div>
                <h1 class="display-title mt-3">Membros e dependentes em uma unica visao</h1>
                <p class="lead-text mt-3">
                    A area de membros agora concentra busca, contexto de filial e uma separacao clara entre associados e dependentes.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                @can('create', \App\Models\Dependent::class)
                    <a href="{{ route('dependentes.create') }}" class="btn-secondary">Novo dependente</a>
                @endcan

                @can('create', \App\Models\Member::class)
                    <a href="{{ route('membros.create') }}" class="btn-primary">Novo associado</a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="stat-card">
            <div class="section-title">Associados encontrados</div>
            <div class="mt-3 text-4xl font-bold text-slate-950">{{ $summary['members'] }}</div>
            <p class="mt-2 text-sm text-slate-600">Titulares dentro do contexto atual de busca.</p>
        </div>
        <div class="stat-card">
            <div class="section-title">Dependentes encontrados</div>
            <div class="mt-3 text-4xl font-bold text-slate-950">{{ $summary['dependents'] }}</div>
            <p class="mt-2 text-sm text-slate-600">Resultados consolidados sem precisar trocar de area.</p>
        </div>
        <div class="stat-card">
            <div class="section-title">Escopo ativo</div>
            <div class="mt-3 text-2xl font-bold text-slate-950">{{ $selectedBranch?->name ?? 'Visao consolidada' }}</div>
            <p class="mt-2 text-sm text-slate-600">
                {{ $selectedBranch ? 'Os dados abaixo respeitam a filial selecionada.' : 'Sem filial filtrada, voce esta vendo o consolidado permitido pelo seu perfil.' }}
            </p>
        </div>
    </div>

    <div class="panel mt-6 p-6">
        <form method="GET" class="grid gap-4 xl:grid-cols-[1.4fr_1fr_1fr_auto]">
            <div>
                <label class="field-label" for="q">Busca unificada</label>
                <input
                    class="field-input"
                    id="q"
                    name="q"
                    type="text"
                    value="{{ $search }}"
                    placeholder="Busque por nome, e-mail, CPF, telefone, titular, parentesco ou filial"
                />
            </div>

            <div>
                <label class="field-label" for="branch_id">Filial</label>
                <select class="field-select" name="branch_id" id="branch_id">
                    <option value="">Todas</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="field-label" for="status">Status</label>
                <select class="field-select" name="status" id="status">
                    <option value="">Todos</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button class="btn-secondary w-full" type="submit">Filtrar</button>
            </div>
        </form>

        @if ($summary['hasFilters'])
            <div class="mt-4 flex flex-wrap gap-2 text-sm">
                @if ($search !== '')
                    <span class="chip-brand">Busca: {{ $search }}</span>
                @endif
                @if (request('status'))
                    <span class="chip-info">Status: {{ collect($statuses)->first(fn ($status) => $status->value === request('status'))?->label() ?? request('status') }}</span>
                @endif
                @if ($selectedBranch)
                    <span class="chip-warning">Filial: {{ $selectedBranch->name }}</span>
                @endif
                <a href="{{ route('membros.index') }}" class="inline-link self-center">Limpar filtros</a>
            </div>
        @endif
    </div>

    <div class="mt-6 grid gap-6">
        <section class="table-shell overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-violet-100/80 px-6 py-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="section-title">Associados</div>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-950">Titulares da base</h2>
                    <p class="mt-2 text-sm text-slate-600">Cada linha mostra o titular com plano, filial principal e acesso rapido a ficha completa.</p>
                </div>
                <div class="chip-brand">{{ $summary['members'] }} resultado(s)</div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="table-header-cell">Associado</th>
                            <th class="table-header-cell">Filial</th>
                            <th class="table-header-cell">Plano</th>
                            <th class="table-header-cell">Contato</th>
                            <th class="table-header-cell">Dependentes</th>
                            <th class="table-header-cell">Status</th>
                            <th class="table-header-cell text-right">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($members as $member)
                            <tr class="table-row">
                                <td class="table-cell">
                                    <div class="font-bold text-slate-900">{{ $member->user->name }}</div>
                                    <div class="mt-1 text-slate-500">{{ $member->user->email }}</div>
                                    <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                        <span class="chip-info">CPF {{ $member->user->cpf ?: 'Nao informado' }}</span>
                                        <span class="chip-brand">Tel {{ $member->user->phone ?: 'Nao informado' }}</span>
                                    </div>
                                </td>
                                <td class="table-cell">{{ $member->primaryBranch->name }}</td>
                                <td class="table-cell">{{ $member->plan?->name ?: '-' }}</td>
                                <td class="table-cell">
                                    <div>{{ $member->user->phone ?: '-' }}</div>
                                    <div class="mt-1 text-slate-500">{{ $member->user->cpf ?: '-' }}</div>
                                </td>
                                <td class="table-cell">{{ $member->dependents_count }}</td>
                                <td class="table-cell">{{ $member->status->label() }}</td>
                                <td class="table-cell text-right">
                                    <a href="{{ route('membros.show', $member) }}" class="inline-link">Ver detalhes</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="table-cell text-center text-slate-500">Nenhum associado encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section id="dependentes" class="table-shell overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-violet-100/80 px-6 py-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="section-title">Dependentes</div>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-950">Dependentes vinculados aos associados</h2>
                    <p class="mt-2 text-sm text-slate-600">A navegacao ficou consistente: dependentes continuam acessiveis, mas dentro do contexto correto de membros.</p>
                </div>
                <div class="chip-info">{{ $summary['dependents'] }} resultado(s)</div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="table-header-cell">Dependente</th>
                            <th class="table-header-cell">Titular</th>
                            <th class="table-header-cell">Filial</th>
                            <th class="table-header-cell">Parentesco</th>
                            <th class="table-header-cell">Contato</th>
                            <th class="table-header-cell">Status</th>
                            <th class="table-header-cell text-right">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dependents as $dependent)
                            <tr class="table-row">
                                <td class="table-cell">
                                    <div class="font-bold text-slate-900">{{ $dependent->user->name }}</div>
                                    <div class="mt-1 text-slate-500">{{ $dependent->user->email }}</div>
                                    <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                        <span class="chip-info">CPF {{ $dependent->user->cpf ?: 'Nao informado' }}</span>
                                        <span class="chip-brand">Tel {{ $dependent->user->phone ?: 'Nao informado' }}</span>
                                    </div>
                                </td>
                                <td class="table-cell">{{ $dependent->member->user->name }}</td>
                                <td class="table-cell">{{ $dependent->branch->name }}</td>
                                <td class="table-cell">{{ $dependent->relationship }}</td>
                                <td class="table-cell">
                                    <div>{{ $dependent->user->phone ?: '-' }}</div>
                                    <div class="mt-1 text-slate-500">{{ $dependent->user->cpf ?: '-' }}</div>
                                </td>
                                <td class="table-cell">{{ $dependent->status->label() }}</td>
                                <td class="table-cell text-right">
                                    <a href="{{ route('dependentes.show', $dependent) }}" class="inline-link">Ver detalhes</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="table-cell text-center text-slate-500">Nenhum dependente encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <div>{{ $members->links() }}</div>
        <div>{{ $dependents->links() }}</div>
    </div>
</x-app-layout>
