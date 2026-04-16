<div class="space-y-6">
    <section class="panel p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="section-title">Reservas da filial</div>
                <h2 class="mt-2 text-2xl font-semibold text-slate-950">Agenda operacional por contexto</h2>
            </div>
            <a href="{{ route('reservas.create', ['branch_id' => $branch->id]) }}" class="btn-primary">Nova reserva</a>
        </div>

        <div class="mt-6 grid gap-4">
            @forelse ($reservations as $reservation)
                @php($statusClass = match($reservation->status->value) {
                    'confirmed' => 'chip-brand',
                    'completed' => 'chip-success',
                    default => 'chip-danger',
                })

                <article class="panel-muted p-6">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <div class="section-title">{{ $reservation->resource->name }}</div>
                            <h3 class="mt-2 text-xl font-semibold text-slate-950">{{ $reservation->member->user->name }}</h3>
                            <p class="mt-2 text-sm text-slate-600">
                                {{ $reservation->reservation_date->format('d/m/Y') }} - {{ $reservation->start_time }} ate {{ $reservation->end_time }}
                            </p>
                        </div>

                        <div class="flex flex-col items-start gap-3 lg:items-end">
                            <div class="{{ $statusClass }}">{{ $reservation->status->label() }}</div>
                            <div class="text-sm font-semibold text-slate-600">R$ {{ number_format((float) $reservation->charged_amount, 2, ',', '.') }}</div>
                        </div>
                    </div>

                    @can('updateStatus', $reservation)
                        <form method="POST" action="{{ route('reservations.status', $reservation) }}" class="mt-5 grid gap-3 lg:grid-cols-[1fr_1fr_auto]">
                            @csrf
                            <select class="field-select" name="status">
                                @foreach (\App\Enums\ReservationStatus::cases() as $status)
                                    <option value="{{ $status->value }}" @selected($reservation->status === $status)>{{ $status->label() }}</option>
                                @endforeach
                            </select>
                            <input class="field-input" name="notes" placeholder="Observacao opcional" value="{{ $reservation->notes }}" />
                            <button class="btn-secondary" type="submit">Atualizar</button>
                        </form>
                    @endcan
                </article>
            @empty
                <div class="empty-state">Nenhuma reserva encontrada nesta filial.</div>
            @endforelse
        </div>
    </section>
</div>
