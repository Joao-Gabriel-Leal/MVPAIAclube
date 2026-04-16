<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="section-title">Associado</div>
                <h1 class="display-title mt-2">{{ $member->user->name }}</h1>
            </div>
            @can('update', $member)
                <a href="{{ route('membros.edit', $member) }}" class="btn-primary">Editar</a>
            @endcan
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-[1fr_0.9fr]">
        <div class="space-y-6">
            <div class="panel p-6">
                <div class="section-title">Resumo</div>
                <div class="mt-5 grid gap-4 text-sm text-slate-700 sm:grid-cols-2">
                    <div><span class="font-semibold text-slate-900">Status:</span> {{ $member->status->label() }}</div>
                    <div><span class="font-semibold text-slate-900">Plano:</span> {{ $member->plan?->name ?: '-' }}</div>
                    <div><span class="font-semibold text-slate-900">Filial principal:</span> {{ $member->primaryBranch->name }}</div>
                    <div><span class="font-semibold text-slate-900">Mensalidade:</span> R$ {{ number_format($member->resolvedMonthlyFee(), 2, ',', '.') }}</div>
                    <div><span class="font-semibold text-slate-900">E-mail:</span> {{ $member->user->email }}</div>
                    <div><span class="font-semibold text-slate-900">Telefone:</span> {{ $member->user->phone }}</div>
                </div>
            </div>

            <div id="dependentes" class="panel p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="section-title">Dependentes</div>
                        <p class="mt-2 text-sm text-slate-500">Os dependentes deste associado ficam centralizados aqui.</p>
                    </div>
                    @can('create', \App\Models\Dependent::class)
                        <a href="{{ route('dependentes.create', ['member_id' => $member->id]) }}" class="btn-secondary">Novo dependente</a>
                    @endcan
                </div>
                <div class="mt-4 space-y-3">
                    @forelse ($member->dependents as $dependent)
                        <div class="flex flex-col gap-4 rounded-2xl border border-slate-100 p-4 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="font-semibold text-slate-900">{{ $dependent->user->name }}</div>
                                <div class="mt-1 text-sm text-slate-600">{{ $dependent->relationship }} - {{ $dependent->status->label() }}</div>
                                <div class="mt-3 space-y-1 text-sm text-slate-500">
                                    <div>{{ $dependent->user->email }}</div>
                                    <div>{{ $dependent->user->phone }}</div>
                                    <div>Filial: {{ $dependent->branch->name }}</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 text-sm font-semibold">
                                <a href="{{ route('dependentes.show', $dependent) }}" class="text-teal-700 hover:text-teal-800">Ver ficha</a>
                                @can('update', $dependent)
                                    <a href="{{ route('dependentes.edit', $dependent) }}" class="text-slate-600 hover:text-slate-900">Editar</a>
                                @endcan
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Nenhum dependente cadastrado.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6">
            @can('approve', $member)
                <div class="panel p-6">
                    <div class="section-title">Atualizar status</div>
                    <form method="POST" action="{{ route('members.status', $member) }}" class="mt-4 space-y-4">
                        @csrf
                        <select class="field-select" name="status">
                            @foreach (\App\Enums\MembershipStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected($member->status === $status)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        <textarea class="field-textarea" name="notes" placeholder="Observacao opcional"></textarea>
                        <button class="btn-primary w-full" type="submit">Salvar status</button>
                    </form>
                </div>
            @endcan

            <div class="panel p-6">
                <div class="section-title">Reservas recentes</div>
                <div class="mt-4 space-y-3">
                    @forelse ($member->reservations->take(5) as $reservation)
                        <div class="rounded-2xl border border-slate-100 p-4">
                            <div class="font-semibold text-slate-900">{{ $reservation->resource->name }}</div>
                            <div class="mt-1 text-sm text-slate-600">{{ $reservation->reservation_date->format('d/m/Y') }} - {{ $reservation->status->label() }}</div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Sem reservas recentes.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
