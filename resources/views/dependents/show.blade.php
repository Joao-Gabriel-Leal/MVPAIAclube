<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="section-title">Dependente</div>
                <h1 class="display-title mt-2">{{ $dependent->user->name }}</h1>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ $backUrl }}" class="btn-secondary">Ver associado</a>
                @can('update', $dependent)
                    <a href="{{ route('dependentes.edit', $dependent) }}" class="btn-primary">Editar</a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-[1fr_0.9fr]">
        <div class="panel p-6">
            <div class="section-title">Resumo</div>
            <div class="mt-5 grid gap-4 sm:grid-cols-2 text-sm text-slate-700">
                <div>
                    <span class="font-semibold text-slate-900">Titular:</span>
                    <a href="{{ $backUrl }}" class="font-semibold text-teal-700 hover:text-teal-800">{{ $dependent->member->user->name }}</a>
                </div>
                <div><span class="font-semibold text-slate-900">Parentesco:</span> {{ $dependent->relationship }}</div>
                <div><span class="font-semibold text-slate-900">Filial:</span> {{ $dependent->branch->name }}</div>
                <div><span class="font-semibold text-slate-900">Status:</span> {{ $dependent->status->label() }}</div>
                <div><span class="font-semibold text-slate-900">E-mail:</span> {{ $dependent->user->email }}</div>
                <div><span class="font-semibold text-slate-900">Telefone:</span> {{ $dependent->user->phone }}</div>
            </div>
        </div>

        @can('approve', $dependent)
            <div class="panel p-6">
                <div class="section-title">Atualizar status</div>
                <form method="POST" action="{{ route('dependents.status', $dependent) }}" class="mt-4 space-y-4">
                    @csrf
                    <select class="field-select" name="status">
                        @foreach (\App\Enums\DependentStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected($dependent->status === $status)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    <button class="btn-primary w-full" type="submit">Salvar status</button>
                </form>
            </div>
        @endcan
    </div>
</x-app-layout>
