<x-app-layout>
    <x-slot name="header">
        <div class="max-w-3xl">
            <div class="section-title">Base social</div>
            <h1 class="display-title mt-3">{{ $member->exists ? 'Editar associado' : 'Novo associado' }}</h1>
            <p class="lead-text mt-3">
                Cadastro organizado por blocos para facilitar a leitura e reduzir ruido visual.
            </p>
        </div>
    </x-slot>

    <form method="POST" action="{{ $member->exists ? route('membros.update', $member) : route('membros.store') }}" class="panel p-6 sm:p-8">
        @csrf
        @if ($member->exists)
            @method('PUT')
        @endif

        <div class="grid gap-8 xl:grid-cols-[1.1fr_0.9fr]">
            <div class="space-y-5">
                <div>
                    <div class="section-title">Dados pessoais</div>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-950">Identificacao principal</h2>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="field-label" for="name">Nome completo</label>
                        <input class="field-input" id="name" name="name" value="{{ old('name', $member->user->name ?? '') }}" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <label class="field-label" for="cpf">CPF</label>
                        <input class="field-input" id="cpf" name="cpf" value="{{ old('cpf', $member->user->cpf ?? '') }}" required />
                        <x-input-error :messages="$errors->get('cpf')" class="mt-2" />
                    </div>

                    <div>
                        <label class="field-label" for="birth_date">Data de nascimento</label>
                        <input class="field-input" id="birth_date" type="date" name="birth_date" value="{{ old('birth_date', optional($member->user->birth_date)->format('Y-m-d')) }}" required />
                        <x-input-error :messages="$errors->get('birth_date')" class="mt-2" />
                    </div>

                    <div>
                        <label class="field-label" for="email">E-mail</label>
                        <input class="field-input" id="email" type="email" name="email" value="{{ old('email', $member->user->email ?? '') }}" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <label class="field-label" for="phone">Telefone</label>
                        <input class="field-input" id="phone" name="phone" value="{{ old('phone', $member->user->phone ?? '') }}" required />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>
                </div>
            </div>

            <div class="space-y-5">
                <div class="panel-muted p-5">
                    <div class="section-title">Acesso</div>
                    <div class="mt-2 text-lg font-semibold text-slate-950">Credenciais e plano</div>
                </div>

                <div>
                    <label class="field-label" for="password">Senha {{ $member->exists ? '(opcional)' : '' }}</label>
                    <input class="field-input" id="password" type="password" name="password" {{ $member->exists ? '' : 'required' }} />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div>
                    <label class="field-label" for="password_confirmation">Confirmar senha</label>
                    <input class="field-input" id="password_confirmation" type="password" name="password_confirmation" {{ $member->exists ? '' : 'required' }} />
                </div>

                <div>
                    <label class="field-label" for="primary_branch_id">Filial principal</label>
                    <select class="field-select" id="primary_branch_id" name="primary_branch_id" required>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(old('primary_branch_id', $member->primary_branch_id) == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('primary_branch_id')" class="mt-2" />
                </div>

                <div>
                    <label class="field-label" for="plan_id">Plano</label>
                    <select class="field-select" id="plan_id" name="plan_id" required>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}" @selected(old('plan_id', $member->plan_id) == $plan->id)>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('plan_id')" class="mt-2" />
                </div>
            </div>
        </div>

        <div class="mt-8 grid gap-5">
            <div>
                <label class="field-label" for="additional_branch_ids">Vinculos adicionais</label>
                <select class="field-select" id="additional_branch_ids" name="additional_branch_ids[]" multiple>
                    @php($selectedBranches = old('additional_branch_ids', $member->exists ? $member->additionalBranches->pluck('id')->all() : []))
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(in_array($branch->id, $selectedBranches))>{{ $branch->name }}</option>
                    @endforeach
                </select>
                <div class="field-hint">Use Ctrl ou Cmd para selecionar mais de uma filial.</div>
            </div>

            <div class="grid gap-5 md:grid-cols-[0.45fr_0.55fr]">
                <div>
                    <label class="field-label" for="custom_monthly_fee">Mensalidade personalizada</label>
                    <input class="field-input" id="custom_monthly_fee" name="custom_monthly_fee" value="{{ old('custom_monthly_fee', $member->custom_monthly_fee) }}" />
                    <x-input-error :messages="$errors->get('custom_monthly_fee')" class="mt-2" />
                </div>

                <div>
                    <label class="field-label" for="notes">Observacoes</label>
                    <textarea class="field-textarea" id="notes" name="notes">{{ old('notes', $member->notes) }}</textarea>
                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                </div>
            </div>
        </div>

        <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('membros.index') }}" class="btn-secondary">Cancelar</a>
            <button class="btn-primary" type="submit">Salvar associado</button>
        </div>
    </form>
</x-app-layout>
