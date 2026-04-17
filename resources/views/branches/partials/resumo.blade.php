<div class="grid gap-6 xl:grid-cols-[1.08fr_0.92fr]">
    <section class="panel p-6">
        <div class="section-title">Resumo da filial</div>
        <h2 class="mt-2 text-2xl font-semibold text-slate-950">O que merece atencao agora</h2>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div class="panel-muted p-5">
                <div class="font-semibold text-slate-900">Aprovacoes e pendencias</div>
                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $highlights['pendingTotal'] }} proposta(s), sendo {{ $highlights['pendingMembers'] }} de associados e {{ $highlights['pendingDependents'] }} de dependentes.</p>
                <a href="{{ route('proposals.index', ['branch_id' => $branch->id]) }}" class="inline-link mt-4 inline-flex">Abrir propostas</a>
            </div>

            <div class="panel-muted p-5">
                <div class="font-semibold text-slate-900">Base social</div>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    {{ $highlights['activeMembers'] }} associados ativos, {{ $highlights['cancelledMembers'] }} cancelados e {{ $highlights['activeDependents'] }} dependentes ativos.
                </p>
                <p class="mt-2 text-xs leading-5 text-slate-500">Cadastros pendentes ficam concentrados apenas em Propostas.</p>
                <a href="{{ route('membros.index', ['branch_id' => $branch->id]) }}" class="inline-link mt-4 inline-flex">Abrir associados</a>
            </div>

            <div class="panel-muted p-5">
                <div class="font-semibold text-slate-900">Recursos</div>
                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $highlights['resources'] }} recurso(s) cadastrados e {{ $highlights['plans'] }} plano(s) conectados a esta filial.</p>
                <a href="{{ route('filiais.show', ['branch' => $branch, 'tab' => 'recursos']) }}" class="inline-link mt-4 inline-flex">Gerenciar recursos</a>
            </div>

            <div class="panel-muted p-5">
                <div class="font-semibold text-slate-900">Estoque</div>
                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $highlights['lowStock'] }} item(ns) em alerta e {{ $inventoryItems->count() }} item(ns) cadastrados.</p>
                <a href="{{ route('filiais.show', ['branch' => $branch, 'tab' => 'estoque']) }}" class="inline-link mt-4 inline-flex">Abrir estoque</a>
            </div>
        </div>
    </section>

    <section class="space-y-6">
        <div class="panel p-6">
            <div class="section-title">Propostas recentes</div>
            <div class="mt-4 space-y-3">
                @forelse ($recentProposals as $proposal)
                    <div class="panel-muted p-4">
                        <div class="font-semibold text-slate-900">{{ $proposal['name'] }}</div>
                        <div class="mt-1 text-sm text-slate-600">{{ $proposal['type_label'] }} - {{ $proposal['context'] }}</div>
                        <div class="mt-2 text-xs text-slate-500">{{ $proposal['origin_label'] }} - {{ $proposal['submitted_age_label'] }}</div>
                    </div>
                @empty
                    <div class="empty-state">Nenhuma proposta pendente nesta filial.</div>
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
