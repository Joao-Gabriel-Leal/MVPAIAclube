<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <div class="section-title">Agenda</div>
                <h1 class="display-title mt-3">Reservas</h1>
                <p class="lead-text mt-3">
                    Visualize status, horarios e valores com cards mais claros e uma leitura mais organizada.
                </p>
            </div>

            <a href="{{ route('reservas.create') }}" class="btn-primary">Nova reserva</a>
        </div>
    </x-slot>

    <div class="grid gap-4">
        @forelse ($reservations as $reservation)
            @php($statusClass = match($reservation->status->value) {
                'confirmed' => 'chip-brand',
                'completed' => 'chip-success',
                default => 'chip-danger',
            })

            <div class="panel p-6">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="section-title">{{ $reservation->branch->name }}</div>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-950">{{ $reservation->resource->name }}</h2>
                        <p class="mt-3 text-sm leading-7 text-slate-600">
                            {{ $reservation->member->user->name }} - {{ $reservation->reservation_date->format('d/m/Y') }} - {{ $reservation->start_time }} ate {{ $reservation->end_time }}
                        </p>
                    </div>

                    <div class="flex flex-col items-start gap-3 lg:items-end">
                        <div class="{{ $statusClass }}">{{ $reservation->status->label() }}</div>
                        <div class="text-sm font-semibold text-slate-600">R$ {{ number_format((float) $reservation->charged_amount, 2, ',', '.') }}</div>
                    </div>
                </div>

                @if ($reservation->notes)
                    <div class="panel-muted mt-5 p-4 text-sm leading-7 text-slate-600">
                        {{ $reservation->notes }}
                    </div>
                @endif

                @can('updateStatus', $reservation)
                    <form method="POST" action="{{ route('reservations.status', $reservation) }}" class="mt-5 grid gap-3 lg:grid-cols-[1fr_1fr_auto]">
                        @csrf

                        <select class="field-select" name="status">
                            @foreach (\App\Enums\ReservationStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected($reservation->status === $status)>{{ $status->label() }}</option>
                            @endforeach
                        </select>

                        <input class="field-input" name="notes" placeholder="Observacao opcional" />

                        <button class="btn-secondary" type="submit">Atualizar</button>
                    </form>
                @endcan
            </div>
        @empty
            <div class="empty-state">Nenhuma reserva encontrada.</div>
        @endforelse
    </div>

    <div class="mt-6">{{ $reservations->links() }}</div>
</x-app-layout>
