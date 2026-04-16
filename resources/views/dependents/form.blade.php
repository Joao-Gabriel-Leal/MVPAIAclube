<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="section-title">Titulares e dependentes</div>
            <h1 class="display-title mt-2">{{ $dependent->exists ? 'Editar dependente' : 'Novo dependente' }}</h1>
        </div>
    </x-slot>

    <form method="POST" action="{{ $dependent->exists ? route('dependentes.update', $dependent) : route('dependentes.store') }}" class="panel p-8 space-y-5">
        @csrf
        @if ($dependent->exists)
            @method('PUT')
        @endif

        <div class="grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="field-label" for="member_id">Titular responsavel</label>
                <select class="field-select" id="member_id" name="member_id" required>
                    @foreach ($members as $member)
                        <option value="{{ $member->id }}" @selected(old('member_id', $dependent->member_id) == $member->id)>{{ $member->user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="field-label" for="branch_id">Filial</label>
                <select class="field-select" id="branch_id" name="branch_id">
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(old('branch_id', $dependent->branch_id) == $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="field-label" for="relationship">Parentesco</label>
                <input class="field-input" id="relationship" name="relationship" value="{{ old('relationship', $dependent->relationship) }}" required />
            </div>
            <div class="md:col-span-2">
                <label class="field-label" for="name">Nome completo</label>
                <input class="field-input" id="name" name="name" value="{{ old('name', $dependent->user->name ?? '') }}" required />
            </div>
            <div>
                <label class="field-label" for="cpf">CPF</label>
                <input class="field-input" id="cpf" name="cpf" value="{{ old('cpf', $dependent->user->cpf ?? '') }}" required />
            </div>
            <div>
                <label class="field-label" for="birth_date">Data de nascimento</label>
                <input class="field-input" id="birth_date" type="date" name="birth_date" value="{{ old('birth_date', optional($dependent->user->birth_date)->format('Y-m-d')) }}" required />
            </div>
            <div>
                <label class="field-label" for="email">E-mail</label>
                <input class="field-input" id="email" type="email" name="email" value="{{ old('email', $dependent->user->email ?? '') }}" required />
            </div>
            <div>
                <label class="field-label" for="phone">Telefone</label>
                <input class="field-input" id="phone" name="phone" value="{{ old('phone', $dependent->user->phone ?? '') }}" required />
            </div>
            <div>
                <label class="field-label" for="password">Senha {{ $dependent->exists ? '(opcional)' : '' }}</label>
                <input class="field-input" id="password" type="password" name="password" {{ $dependent->exists ? '' : 'required' }} />
            </div>
            <div>
                <label class="field-label" for="password_confirmation">Confirmar senha</label>
                <input class="field-input" id="password_confirmation" type="password" name="password_confirmation" {{ $dependent->exists ? '' : 'required' }} />
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ $backUrl ?? route('membros.index') }}" class="btn-secondary">Cancelar</a>
            <button class="btn-primary" type="submit">Salvar dependente</button>
        </div>
    </form>
</x-app-layout>
