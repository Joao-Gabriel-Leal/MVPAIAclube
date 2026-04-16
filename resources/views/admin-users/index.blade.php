<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="section-title">Operacao da rede</div>
            <h1 class="display-title mt-2">Admins de filial</h1>
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
        <div class="panel p-6">
            <div class="section-title">Cadastrar admin</div>
            <form method="POST" action="{{ route('admin-users.store') }}" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label class="field-label" for="name">Nome</label>
                    <input class="field-input" id="name" name="name" value="{{ old('name') }}" required />
                </div>
                <div>
                    <label class="field-label" for="email">E-mail</label>
                    <input class="field-input" id="email" type="email" name="email" value="{{ old('email') }}" required />
                </div>
                <div>
                    <label class="field-label" for="phone">Telefone</label>
                    <input class="field-input" id="phone" name="phone" value="{{ old('phone') }}" />
                </div>
                <div>
                    <label class="field-label" for="branch_id">Filial</label>
                    <select class="field-select" id="branch_id" name="branch_id" required>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="field-label" for="password">Senha</label>
                        <input class="field-input" id="password" type="password" name="password" required />
                    </div>
                    <div>
                        <label class="field-label" for="password_confirmation">Confirmar senha</label>
                        <input class="field-input" id="password_confirmation" type="password" name="password_confirmation" required />
                    </div>
                </div>
                <button class="btn-primary w-full" type="submit">Salvar admin</button>
            </form>
        </div>

        <div class="panel p-6">
            <div class="section-title">Equipe cadastrada</div>
            <div class="mt-5 space-y-3">
                @foreach ($admins as $admin)
                    <div class="rounded-2xl border border-slate-100 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-semibold text-slate-900">{{ $admin->name }}</div>
                                <div class="mt-1 text-sm text-slate-600">{{ $admin->email }}</div>
                            </div>
                            <div class="chip-info">{{ $admin->branch?->name }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
