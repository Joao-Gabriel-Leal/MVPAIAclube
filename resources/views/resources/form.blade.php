<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="section-title">Agenda e recursos</div>
            <h1 class="display-title mt-2">{{ $clubResource->exists ? 'Editar recurso' : 'Novo recurso' }}</h1>
        </div>
    </x-slot>

    <form method="POST" action="{{ $clubResource->exists ? route('recursos.update', $clubResource) : route('recursos.store') }}" class="panel p-8 space-y-6">
        @csrf
        @if ($clubResource->exists)
            @method('PUT')
        @endif

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="field-label" for="branch_id">Filial</label>
                <select class="field-select" id="branch_id" name="branch_id" required>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(old('branch_id', $clubResource->branch_id) == $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="field-label" for="name">Nome</label>
                <input class="field-input" id="name" name="name" value="{{ old('name', $clubResource->name) }}" required />
            </div>
            <div>
                <label class="field-label" for="type">Tipo</label>
                <input class="field-input" id="type" name="type" value="{{ old('type', $clubResource->type) }}" />
            </div>
            <div>
                <label class="field-label" for="max_capacity">Capacidade maxima</label>
                <input class="field-input" id="max_capacity" name="max_capacity" value="{{ old('max_capacity', $clubResource->max_capacity) }}" required />
            </div>
            <div>
                <label class="field-label" for="default_price">Valor padrao</label>
                <input class="field-input" id="default_price" name="default_price" value="{{ old('default_price', $clubResource->default_price) }}" required />
            </div>
            <div class="flex items-center gap-3 pt-8">
                <input id="is_active" type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-teal-700 focus:ring-teal-500" @checked(old('is_active', $clubResource->is_active ?? true))>
                <label for="is_active" class="text-sm font-medium text-slate-700">Recurso ativo</label>
            </div>
            <div class="md:col-span-2">
                <label class="field-label" for="description">Descricao</label>
                <textarea class="field-textarea" id="description" name="description">{{ old('description', $clubResource->description) }}</textarea>
            </div>
            <div class="md:col-span-2">
                <label class="field-label" for="allowed_plan_ids">Planos permitidos</label>
                <select class="field-select" id="allowed_plan_ids" name="allowed_plan_ids[]" multiple>
                    @php($selectedPlans = old('allowed_plan_ids', $clubResource->exists ? $clubResource->plans->pluck('id')->all() : []))
                    @foreach ($plans as $plan)
                        <option value="{{ $plan->id }}" @selected(in_array($plan->id, $selectedPlans))>{{ $plan->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="panel-muted p-6">
            <div class="section-title">Horarios disponiveis</div>
            <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @php($scheduleMap = $clubResource->exists ? $clubResource->schedules->keyBy('day_of_week') : collect())
                @foreach ([0 => 'Dom', 1 => 'Seg', 2 => 'Ter', 3 => 'Qua', 4 => 'Qui', 5 => 'Sex', 6 => 'Sab'] as $day => $label)
                    @php($schedule = $scheduleMap->get($day))
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="font-semibold text-slate-900">{{ $label }}</div>
                        <input type="hidden" name="schedules[{{ $day }}][day_of_week]" value="{{ $day }}">
                        <div class="mt-3 space-y-3">
                            <input class="field-input" type="time" name="schedules[{{ $day }}][opens_at]" value="{{ old("schedules.$day.opens_at", $schedule->opens_at ?? '08:00') }}">
                            <input class="field-input" type="time" name="schedules[{{ $day }}][closes_at]" value="{{ old("schedules.$day.closes_at", $schedule->closes_at ?? '22:00') }}">
                            <input class="field-input" type="number" min="15" step="15" name="schedules[{{ $day }}][slot_interval_minutes]" value="{{ old("schedules.$day.slot_interval_minutes", $schedule->slot_interval_minutes ?? 60) }}">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="panel-muted p-6">
            <div class="section-title">Bloqueio manual</div>
            <div class="mt-5 grid gap-4 md:grid-cols-4">
                <input class="field-input" type="date" name="block_date" value="{{ old('block_date') }}">
                <input class="field-input" type="time" name="block_start_time" value="{{ old('block_start_time') }}">
                <input class="field-input" type="time" name="block_end_time" value="{{ old('block_end_time') }}">
                <input class="field-input" name="block_reason" value="{{ old('block_reason') }}" placeholder="Motivo do bloqueio">
            </div>

            @if ($clubResource->exists && $clubResource->blocks->isNotEmpty())
                <div class="mt-5 space-y-2 text-sm text-slate-700">
                    @foreach ($clubResource->blocks->sortByDesc('block_date')->take(6) as $block)
                        <div class="rounded-2xl bg-white px-4 py-3">
                            {{ $block->block_date->format('d/m/Y') }} · {{ $block->start_time }} - {{ $block->end_time }} · {{ $block->reason ?: 'Bloqueio manual' }}
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('recursos.index') }}" class="btn-secondary">Cancelar</a>
            <button class="btn-primary" type="submit">Salvar recurso</button>
        </div>
    </form>
</x-app-layout>
