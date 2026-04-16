<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="section-title">Configuracao comercial</div>
            <h1 class="display-title mt-2">{{ $plan->exists ? 'Editar plano' : 'Novo plano' }}</h1>
        </div>

        <a href="{{ route('plans.index') }}" class="btn-secondary">Voltar para planos</a>
    </x-slot>

    @php($selectedResourceIds = collect(old('resource_ids', $plan->exists ? $plan->resources->pluck('id')->all() : []))->map(fn ($id) => (int) $id)->all())
    @php($defaultActive = $plan->exists ? (int) $plan->is_active : 1)
    @php($defaultInheritBenefits = $plan->exists ? (int) $plan->dependents_inherit_benefits : 0)
    @php($priceValue = old('base_price', $plan->base_price))

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
        <div class="space-y-6">
            @if ($errors->any())
                <div class="panel border-rose-100 bg-rose-50/90 px-5 py-4 text-sm text-rose-700">
                    <div class="font-semibold">Revise os campos destacados antes de salvar.</div>
                    <div class="mt-1">{{ $errors->first() }}</div>
                </div>
            @endif

            <form method="POST" action="{{ $plan->exists ? route('plans.update', $plan) : route('plans.store') }}" class="space-y-6">
                @csrf
                @if ($plan->exists)
                    @method('PUT')
                @endif

                <section class="panel p-8">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <div class="section-title">Identidade do plano</div>
                            <h2 class="mt-2 text-2xl font-semibold text-slate-900">Informacoes principais</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                Defina nome, descricao e se o plano fica disponivel para novas adesoes.
                            </p>
                        </div>

                        @if ($plan->exists)
                            <span class="{{ $plan->is_active ? 'chip-success' : 'chip-warning' }}">
                                {{ $plan->is_active ? 'Plano ativo' : 'Plano inativo' }}
                            </span>
                        @endif
                    </div>

                    <div class="mt-6 grid gap-5 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="field-label" for="name">Nome do plano</label>
                            <input class="field-input" id="name" name="name" value="{{ old('name', $plan->name) }}" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="md:col-span-2">
                            <label class="field-label" for="description">Descricao</label>
                            <textarea class="field-textarea" id="description" name="description" placeholder="Explique rapidamente quem deve usar este plano e quais beneficios ele oferece.">{{ old('description', $plan->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                            <input type="hidden" name="is_active" value="0">
                            <input
                                id="is_active"
                                type="checkbox"
                                name="is_active"
                                value="1"
                                class="mt-1 rounded border-slate-300 text-teal-700 focus:ring-teal-500"
                                @checked(old('is_active', $defaultActive))
                            >
                            <div>
                                <label for="is_active" class="text-sm font-semibold text-slate-900">Plano ativo</label>
                                <p class="mt-1 text-sm text-slate-600">Planos inativos deixam de aparecer em novas adesoes, mas continuam registrados.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="panel p-8">
                    <div>
                        <div class="section-title">Comercial e beneficios</div>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-900">Preco e regras do plano</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Ajuste os limites principais do plano e como os descontos extras de reserva devem funcionar.
                        </p>
                    </div>

                    <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                        <div>
                            <label class="field-label" for="base_price">Valor base</label>
                            <input class="field-input" id="base_price" type="number" min="0" step="0.01" name="base_price" value="{{ $priceValue }}" placeholder="0,00" />
                            <x-input-error :messages="$errors->get('base_price')" class="mt-2" />
                        </div>

                        <div>
                            <label class="field-label" for="dependent_limit">Limite de dependentes</label>
                            <input class="field-input" id="dependent_limit" type="number" min="0" step="1" name="dependent_limit" value="{{ old('dependent_limit', $plan->dependent_limit ?? 0) }}" required />
                            <x-input-error :messages="$errors->get('dependent_limit')" class="mt-2" />
                        </div>

                        <div>
                            <label class="field-label" for="guest_limit_per_reservation">Convidados por reserva</label>
                            <input class="field-input" id="guest_limit_per_reservation" type="number" min="0" step="1" name="guest_limit_per_reservation" value="{{ old('guest_limit_per_reservation', $plan->guest_limit_per_reservation ?? 0) }}" required />
                            <x-input-error :messages="$errors->get('guest_limit_per_reservation')" class="mt-2" />
                        </div>

                        <div>
                            <label class="field-label" for="free_reservations_per_month">Reservas gratis por mes</label>
                            <input class="field-input" id="free_reservations_per_month" type="number" min="0" step="1" name="free_reservations_per_month" value="{{ old('free_reservations_per_month', $plan->free_reservations_per_month ?? 0) }}" required />
                            <x-input-error :messages="$errors->get('free_reservations_per_month')" class="mt-2" />
                        </div>

                        <div>
                            <label class="field-label" for="extra_reservation_discount_type">Tipo de desconto</label>
                            <select class="field-select" id="extra_reservation_discount_type" name="extra_reservation_discount_type" required>
                                @foreach (\App\Enums\DiscountType::cases() as $discountType)
                                    <option
                                        value="{{ $discountType->value }}"
                                        @selected(old('extra_reservation_discount_type', $plan->extra_reservation_discount_type?->value ?? \App\Enums\DiscountType::None->value) === $discountType->value)
                                    >
                                        {{ $discountType->label() }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('extra_reservation_discount_type')" class="mt-2" />
                        </div>

                        <div>
                            <label class="field-label" for="extra_reservation_discount_value">Valor do desconto</label>
                            <input class="field-input" id="extra_reservation_discount_value" type="number" min="0" step="0.01" name="extra_reservation_discount_value" value="{{ old('extra_reservation_discount_value', $plan->extra_reservation_discount_value ?? 0) }}" required />
                            <p class="mt-2 text-xs text-slate-500">Use 0 quando o plano nao aplicar desconto extra.</p>
                            <x-input-error :messages="$errors->get('extra_reservation_discount_value')" class="mt-2" />
                        </div>

                        <div class="md:col-span-2 xl:col-span-2">
                            <div class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <input type="hidden" name="dependents_inherit_benefits" value="0">
                                <input
                                    id="dependents_inherit_benefits"
                                    type="checkbox"
                                    name="dependents_inherit_benefits"
                                    value="1"
                                    class="mt-1 rounded border-slate-300 text-teal-700 focus:ring-teal-500"
                                    @checked(old('dependents_inherit_benefits', $defaultInheritBenefits))
                                >
                                <div>
                                    <label for="dependents_inherit_benefits" class="text-sm font-semibold text-slate-900">Dependentes herdam beneficios</label>
                                    <p class="mt-1 text-sm text-slate-600">Marque se os dependentes podem usar os mesmos recursos e descontos do titular.</p>
                                </div>
                            </div>
                            <x-input-error :messages="$errors->get('dependents_inherit_benefits')" class="mt-2" />
                        </div>
                    </div>
                </section>

                <section class="panel p-8">
                    <div>
                        <div class="section-title">Recursos permitidos</div>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-900">Escolha onde este plano pode reservar</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Os recursos foram agrupados por filial para ficar mais facil liberar acessos sem depender de uma lista multipla confusa.
                        </p>
                    </div>

                    <div class="mt-6 space-y-4">
                        @forelse ($resourceGroups as $branchName => $branchResources)
                            <div class="rounded-3xl border border-slate-200 bg-slate-50/80 p-5">
                                <div class="flex items-center justify-between gap-3">
                                    <h3 class="text-lg font-semibold text-slate-900">{{ $branchName }}</h3>
                                    <span class="text-sm text-slate-500">{{ $branchResources->count() }} recursos</span>
                                </div>

                                <div class="mt-4 grid gap-3 md:grid-cols-2">
                                    @foreach ($branchResources as $resource)
                                        <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-4">
                                            <input
                                                type="checkbox"
                                                name="resource_ids[]"
                                                value="{{ $resource->id }}"
                                                class="mt-1 rounded border-slate-300 text-teal-700 focus:ring-teal-500"
                                                @checked(in_array($resource->id, $selectedResourceIds))
                                            >
                                            <div>
                                                <div class="text-sm font-semibold text-slate-900">{{ $resource->name }}</div>
                                                <div class="mt-1 text-xs text-slate-500">
                                                    {{ $resource->type ?: 'Recurso do clube' }}
                                                    @if (! $resource->is_active)
                                                        - inativo
                                                    @endif
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-5 py-6 text-sm text-slate-600">
                                Nenhum recurso foi cadastrado ainda. Cadastre os recursos do clube para liberar acessos nos planos.
                            </div>
                        @endforelse
                    </div>

                    <x-input-error :messages="$errors->get('resource_ids')" class="mt-4" />
                    <x-input-error :messages="$errors->get('resource_ids.*')" class="mt-2" />
                </section>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <a href="{{ route('plans.index') }}" class="btn-secondary">Cancelar</a>
                    <button class="btn-primary" type="submit">{{ $plan->exists ? 'Salvar alteracoes' : 'Cadastrar plano' }}</button>
                </div>
            </form>

            @if ($plan->exists)
                <section class="panel border-rose-100 bg-rose-50/80 p-6">
                    <div class="text-xs font-semibold uppercase tracking-[0.3em] text-rose-700">Zona de risco</div>
                    <h2 class="mt-2 text-xl font-semibold text-slate-900">Excluir plano</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        A exclusao so fica disponivel quando nao houver associados vinculados a este plano.
                    </p>

                    @if ($errors->has('delete'))
                        <div class="mt-4 rounded-2xl border border-rose-200 bg-white px-4 py-3 text-sm text-rose-700">
                            {{ $errors->first('delete') }}
                        </div>
                    @endif

                    <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-sm text-slate-600">
                            {{ $plan->members_count > 0 ? 'Existem associados vinculados a este plano.' : 'Nenhum associado vinculado. Exclusao liberada.' }}
                        </div>

                        <form method="POST" action="{{ route('plans.destroy', $plan) }}" onsubmit="return confirm('Tem certeza que deseja excluir este plano? Esta acao nao pode ser desfeita.');">
                            @csrf
                            @method('DELETE')
                            <button class="btn-danger" type="submit" @disabled($plan->members_count > 0)>Excluir plano</button>
                        </form>
                    </div>
                </section>
            @endif
        </div>

        <aside class="space-y-6 xl:sticky xl:top-24 xl:self-start">
            <section class="panel p-6">
                <div class="section-title">Resumo rapido</div>
                <div class="mt-4 space-y-4">
                    <div>
                        <div class="text-sm text-slate-500">Status</div>
                        <div class="mt-1 font-semibold text-slate-900">
                            {{ old('is_active', $defaultActive) ? 'Ativo para novas adesoes' : 'Inativo para novas adesoes' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-slate-500">Associados vinculados</div>
                        <div class="mt-1 font-semibold text-slate-900">{{ $plan->members_count }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-slate-500">Recursos liberados</div>
                        <div class="mt-1 font-semibold text-slate-900">{{ count($selectedResourceIds) }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-slate-500">Valor base atual</div>
                        <div class="mt-1 font-semibold text-slate-900">
                            {{ $priceValue !== null && $priceValue !== '' ? 'R$ '.number_format((float) $priceValue, 2, ',', '.') : 'Nao definido' }}
                        </div>
                    </div>
                </div>
            </section>

            <section class="panel-muted p-6">
                <div class="section-title">Guia rapido</div>
                <div class="mt-4 space-y-3 text-sm leading-6 text-slate-600">
                    <p>1. Preencha o nome e deixe o plano ativo somente quando ele puder ser vendido.</p>
                    <p>2. Configure limites de dependentes, convidados e reservas gratis conforme a regra comercial.</p>
                    <p>3. Marque os recursos permitidos por filial para evitar liberar acessos indevidos.</p>
                </div>
            </section>
        </aside>
    </div>
</x-app-layout>
