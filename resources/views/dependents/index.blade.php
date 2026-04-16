<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="section-title">Titulares e dependentes</div>
                <h1 class="display-title mt-2">Dependentes</h1>
            </div>
            @can('create', \App\Models\Dependent::class)
                <a href="{{ route('dependentes.create') }}" class="btn-primary">Novo dependente</a>
            @endcan
        </div>
    </x-slot>

    <div class="panel p-6">
        <form method="GET" class="grid gap-4 md:grid-cols-2">
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
    </div>

    <div class="mt-6 overflow-hidden panel">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-stone-50 text-left text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Dependente</th>
                        <th class="px-6 py-4">Titular</th>
                        <th class="px-6 py-4">Filial</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Acoes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($dependents as $dependent)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900">{{ $dependent->user->name }}</div>
                                <div class="text-slate-500">{{ $dependent->relationship }}</div>
                            </td>
                            <td class="px-6 py-4">{{ $dependent->member->user->name }}</td>
                            <td class="px-6 py-4">{{ $dependent->branch->name }}</td>
                            <td class="px-6 py-4">{{ $dependent->status->label() }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('dependentes.show', $dependent) }}" class="text-sm font-semibold text-teal-700 hover:text-teal-800">Ver detalhes</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">Nenhum dependente encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $dependents->links() }}</div>
</x-app-layout>
