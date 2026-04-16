<x-app-layout>
    <x-slot name="header">
        <div class="max-w-3xl">
            <div class="section-title">Agenda de reservas</div>
            <h1 class="display-title mt-3">Nova reserva</h1>
            <p class="lead-text mt-3">
                Escolha o recurso, navegue pelo calendario e confirme um horario livre sem precisar digitar a faixa manualmente.
            </p>
        </div>
    </x-slot>

    @if ($errors->has('reservation'))
        <div class="status-banner status-banner-error mb-6">
            {{ $errors->first('reservation') }}
        </div>
    @endif

    <form method="POST" action="{{ route('reservas.store') }}" class="grid gap-6 xl:grid-cols-[0.88fr_1.12fr]">
        @csrf

        <input id="reservation_date" type="hidden" name="reservation_date" value="{{ old('reservation_date') }}" />
        <input id="start_time" type="hidden" name="start_time" value="{{ old('start_time') }}" />
        <input id="end_time" type="hidden" name="end_time" value="{{ old('end_time') }}" />

        <section class="panel p-6 sm:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="section-title">Configuracao</div>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-950">Detalhes da reserva</h2>
                </div>
                <div class="chip-brand">Reserva guiada</div>
            </div>

            <div class="mt-8 space-y-5">
                <div>
                    <label class="field-label" for="club_resource_id">Recurso</label>
                    <select class="field-select" id="club_resource_id" name="club_resource_id" required>
                        @foreach ($resources as $resource)
                            <option value="{{ $resource->id }}" @selected(old('club_resource_id', $resources->first()?->id) == $resource->id)>{{ $resource->name }} - {{ $resource->branch->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('club_resource_id')" class="mt-2" />
                </div>

                @if (auth()->user()->isAdminMatrix() || auth()->user()->isAdminBranch())
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="field-label" for="member_id">Associado responsavel</label>
                            <select class="field-select" id="member_id" name="member_id">
                                <option value="">Selecione</option>
                                @foreach ($members as $member)
                                    <option value="{{ $member->id }}" @selected(old('member_id') == $member->id)>{{ $member->user->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('member_id')" class="mt-2" />
                        </div>

                        <div>
                            <label class="field-label" for="dependent_id">Dependente</label>
                            <select class="field-select" id="dependent_id" name="dependent_id">
                                <option value="">Sem dependente</option>
                                @foreach ($dependents as $dependent)
                                    <option value="{{ $dependent->id }}" @selected(old('dependent_id') == $dependent->id)>{{ $dependent->user->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('dependent_id')" class="mt-2" />
                        </div>
                    </div>
                @endif

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="field-label" for="guest_count">Convidados</label>
                        <input class="field-input" id="guest_count" type="number" min="0" name="guest_count" value="{{ old('guest_count', 0) }}" />
                        <x-input-error :messages="$errors->get('guest_count')" class="mt-2" />
                    </div>

                    <div class="panel-muted p-5">
                        <div class="section-title">Como funciona</div>
                        <p class="mt-3 text-sm leading-7 text-slate-600">
                            Dias passados e sem agenda ficam bloqueados. Dias com agenda parcial mostram somente os slots ainda livres.
                        </p>
                    </div>
                </div>

                <div>
                    <label class="field-label" for="notes">Observacoes</label>
                    <textarea class="field-textarea" id="notes" name="notes" placeholder="Anote informacoes importantes para essa reserva.">{{ old('notes') }}</textarea>
                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                </div>
            </div>
        </section>

        <section class="space-y-6" data-reservation-ui data-today="{{ now()->toDateString() }}">
            <div class="panel p-6 sm:p-8">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <div class="section-title">Selecione a data</div>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-950">Calendario de disponibilidade</h2>
                    </div>

                    <div class="calendar-legend">
                        <div class="calendar-legend-item">
                            <span class="calendar-legend-swatch bg-emerald-500"></span>
                            Livre
                        </div>
                        <div class="calendar-legend-item">
                            <span class="calendar-legend-swatch bg-amber-400"></span>
                            Parcial
                        </div>
                        <div class="calendar-legend-item">
                            <span class="calendar-legend-swatch bg-rose-300"></span>
                            Indisponivel
                        </div>
                    </div>
                </div>

                <div class="calendar-shell mt-6" data-calendar-shell>
                    <div class="calendar-toolbar">
                        <button type="button" class="btn-secondary h-11 w-11 rounded-full p-0" data-calendar-prev aria-label="Mes anterior">
                            <span aria-hidden="true">&larr;</span>
                        </button>
                        <div class="calendar-month-badge">
                            <div class="text-center text-2xl font-bold capitalize text-slate-950" data-calendar-month-label></div>
                            <p class="calendar-helper">Toque em um dia com destaque para ver os horarios livres.</p>
                        </div>
                        <button type="button" class="btn-secondary h-11 w-11 rounded-full p-0" data-calendar-next aria-label="Proximo mes">
                            <span aria-hidden="true">&rarr;</span>
                        </button>
                    </div>

                    <p class="calendar-status mt-5" data-calendar-status>Selecione um recurso para carregar a agenda.</p>

                    <div class="calendar-grid-shell mt-6">
                        <div class="calendar-grid" data-calendar-grid></div>
                    </div>
                </div>
            </div>

            <div class="panel p-6 sm:p-8" data-slot-panel>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="section-title">Horarios disponiveis</div>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-950">Escolha seu slot</h2>
                    </div>
                    <div class="chip-info">Confirmacao instantanea</div>
                </div>

                <div class="selection-summary mt-6" data-selection-summary></div>

                <div class="slot-stage mt-6" data-slot-stage>
                    <p class="slot-status" data-slot-status>Escolha um dia no calendario para visualizar os horarios.</p>
                    <div class="slot-grid mt-4" data-slot-grid></div>
                </div>

                <button class="btn-primary mt-8 w-full" type="submit" data-reservation-submit disabled>
                    Selecione um horario
                </button>
            </div>
        </section>
    </form>
</x-app-layout>
