<x-app-layout>
    <x-slot name="header">
        <div class="max-w-3xl">
            <div class="section-title">Agenda de reservas</div>
            <h1 class="display-title mt-3">Nova reserva</h1>
        </div>
    </x-slot>

    @if ($errors->has('reservation'))
        <div class="status-banner status-banner-error mb-6">
            {{ $errors->first('reservation') }}
        </div>
    @endif

    <form method="POST" action="{{ route('reservas.store') }}" class="grid gap-5 xl:grid-cols-[0.9fr_1.1fr]">
        @csrf

        <input id="reservation_date" type="hidden" name="reservation_date" value="{{ old('reservation_date') }}" />
        <input id="start_time" type="hidden" name="start_time" value="{{ old('start_time') }}" />
        <input id="end_time" type="hidden" name="end_time" value="{{ old('end_time') }}" />

        <section class="panel p-5 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="section-title">Configuracao</div>
                    <h2 class="mt-2 text-xl font-semibold text-slate-950">Detalhes da reserva</h2>
                </div>
                <div class="chip-brand">Reserva guiada</div>
            </div>

            <div class="mt-6 space-y-[1.125rem]">
                <div>
                    <label class="field-label" for="club_resource_id">Recurso</label>
                    <select class="field-select" id="club_resource_id" name="club_resource_id" @disabled($resources->isEmpty()) required>
                        @if ($resources->isEmpty())
                            <option value="" selected disabled>Nenhum recurso disponivel para as filiais do cadastro.</option>
                        @else
                            @foreach ($resources as $resource)
                                <option value="{{ $resource->id }}" @selected(old('club_resource_id', $resources->first()?->id) == $resource->id)>{{ $resource->name }} - {{ $resource->branch->name }}</option>
                            @endforeach
                        @endif
                    </select>
                    <x-input-error :messages="$errors->get('club_resource_id')" class="mt-2" />
                </div>

                @if (auth()->user()->isAdminMatrix() || auth()->user()->isAdminBranch())
                    <div class="grid gap-4 md:grid-cols-2">
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

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="field-label" for="guest_count">Convidados</label>
                        <input class="field-input" id="guest_count" type="number" min="0" name="guest_count" value="{{ old('guest_count', 0) }}" />
                        <x-input-error :messages="$errors->get('guest_count')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <label class="field-label" for="notes">Observacoes</label>
                    <textarea class="field-textarea" id="notes" name="notes" placeholder="Anote informacoes importantes para essa reserva.">{{ old('notes') }}</textarea>
                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                </div>
            </div>
        </section>

        <section class="reservation-booking-grid" data-reservation-ui data-today="{{ now()->toDateString() }}">
            <div class="reservation-card">
                <div class="reservation-card-heading">
                    <div class="reservation-card-label">
                        <span class="reservation-card-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="4" y="5" width="16" height="15" rx="2"></rect>
                                <path d="M8 3v4M16 3v4M4 10h16"></path>
                            </svg>
                        </span>
                        <h2>Selecione a Data</h2>
                    </div>
                </div>

                <div class="calendar-shell mt-4" data-calendar-shell>
                    <div class="calendar-toolbar">
                        <button type="button" class="calendar-nav-button" data-calendar-prev aria-label="Mes anterior">
                            <span aria-hidden="true">&#8249;</span>
                        </button>
                        <div class="calendar-month-label" data-calendar-month-label></div>
                        <button type="button" class="calendar-nav-button" data-calendar-next aria-label="Proximo mes">
                            <span aria-hidden="true">&#8250;</span>
                        </button>
                    </div>

                    <div class="calendar-grid-shell mt-4">
                        <div class="calendar-grid" data-calendar-grid></div>
                    </div>

                    <p class="calendar-status mt-4" data-calendar-status aria-live="polite" aria-atomic="true">Selecione um recurso para carregar a agenda.</p>
                </div>
            </div>

            <div class="reservation-card" data-slot-panel>
                <div class="reservation-card-heading">
                    <div class="reservation-card-label">
                        <span class="reservation-card-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="8"></circle>
                                <path d="M12 8v4l2.5 2.5"></path>
                            </svg>
                        </span>
                        <h2>Horários Disponíveis</h2>
                    </div>
                </div>

                <div class="slot-stage mt-4" data-slot-stage>
                    <p class="slot-status" data-slot-status aria-live="polite" aria-atomic="true">Escolha uma data com horários disponíveis.</p>
                    <div class="slot-grid mt-3.5" data-slot-grid></div>
                </div>

                <button class="btn-primary reservation-submit mt-5 hidden w-full" type="submit" data-reservation-submit disabled>
                    Confirmar horário
                </button>
            </div>
        </section>
    </form>
</x-app-layout>
