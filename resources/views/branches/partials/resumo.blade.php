<div class="grid gap-6 xl:grid-cols-[1.08fr_0.92fr]">
    <section class="panel p-6">
        <div class="section-title">Resumo da filial</div>
        <h2 class="mt-2 text-2xl font-semibold text-slate-950">O que merece atencao agora</h2>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div class="panel-muted p-5">
                <div class="font-semibold text-slate-900">Aprovacoes e pendencias</div>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    {{ $highlights['pendingMembers'] }} associado(s) e {{ $highlights['pendingDependents'] }} dependente(s) aguardando validacao.
                </p>
                <a href="{{ route('membros.index', ['branch_id' => $branch->id, 'status' => 'pending']) }}" class="inline-link mt-4 inline-flex">Ver pendentes</a>
            </div>

            <div class="panel-muted p-5">
                <div class="font-semibold text-slate-900">Base social</div>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    {{ $highlights['members'] }} associados e {{ $highlights['dependents'] }} dependentes concentrados nesta unidade.
                </p>
                <a href="{{ route('membros.index', ['branch_id' => $branch->id]) }}" class="inline-link mt-4 inline-flex">Abrir associados</a>
            </div>

            <div class="panel-muted p-5">
                <div class="font-semibold text-slate-900">Recursos</div>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    {{ $highlights['resources'] }} recurso(s) vinculados a esta operacao, com leitura mais clara por unidade.
                </p>
                <a href="{{ route('filiais.show', ['branch' => $branch, 'tab' => 'recursos']) }}" class="inline-link mt-4 inline-flex">Gerenciar recursos</a>
            </div>

            <div class="panel-muted p-5">
                <div class="font-semibold text-slate-900">Relatorios</div>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Graficos, tendencia e leitura gerencial para entender o desempenho da filial com mais rapidez.
                </p>
                <a href="{{ route('filiais.show', ['branch' => $branch, 'tab' => 'relatorios']) }}" class="inline-link mt-4 inline-flex">Abrir relatorios</a>
            </div>
        </div>
    </section>

    <section class="space-y-6">
        <div class="panel p-6">
            <div class="section-title">Associados recentes</div>
            <div class="mt-4 space-y-3">
                @forelse ($members->take(5) as $member)
                    <div class="panel-muted p-4">
                        <div class="font-semibold text-slate-900">{{ $member->user->name }}</div>
                        <div class="mt-1 text-sm text-slate-600">{{ $member->plan?->name ?: 'Sem plano' }} - {{ $member->status->label() }}</div>
                        <div class="mt-2 text-xs text-slate-500">{{ $member->user->email }} - {{ $member->user->phone ?: 'telefone nao informado' }}</div>
                    </div>
                @empty
                    <div class="empty-state">Nenhum associado cadastrado nesta filial.</div>
                @endforelse
            </div>
        </div>

        <div class="panel p-6">
            <div class="section-title">Reservas recentes</div>
            <div class="mt-4 space-y-3">
                @forelse ($reservations->take(5) as $reservation)
                    <div class="panel-muted p-4">
                        <div class="font-semibold text-slate-900">{{ $reservation->resource->name }}</div>
                        <div class="mt-1 text-sm text-slate-600">{{ $reservation->member->user->name }} - {{ $reservation->reservation_date->format('d/m/Y') }}</div>
                        <div class="mt-2 text-xs text-slate-500">{{ $reservation->start_time }} ate {{ $reservation->end_time }} - {{ $reservation->status->label() }}</div>
                    </div>
                @empty
                    <div class="empty-state">Nenhuma reserva encontrada para a filial.</div>
                @endforelse
            </div>
        </div>
    </section>
</div>
