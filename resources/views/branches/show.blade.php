<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
            <div class="max-w-4xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="{{ $branch->type->value === 'headquarters' ? 'chip-info' : 'chip-warning' }}">{{ $branch->type->label() }}</span>
                    <span class="{{ $branch->is_active ? 'chip-success' : 'chip-danger' }}">
                        {{ $branch->is_active ? 'Filial ativa' : 'Filial inativa' }}
                    </span>
                </div>
                <h1 class="display-title mt-3">{{ $branch->name }}</h1>
                <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-600">
                    {{ $branch->address ?: 'Endereco nao informado.' }}{{ $branch->phone ? ' - '.$branch->phone : '' }}{{ $branch->email ? ' - '.$branch->email : '' }}
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                @can('update', $branch)
                    <a href="{{ route('filiais.edit', $branch) }}" class="btn-secondary">Editar filial</a>
                @endcan
            </div>
        </div>
    </x-slot>

    @php($tabQuery = request()->except('tab'))

    <div class="branch-tab-nav">
        @foreach ($tabs as $tabKey => $tabLabel)
            <a href="{{ route('filiais.show', array_merge(['branch' => $branch, 'tab' => $tabKey], $tabQuery)) }}" class="{{ $tab === $tabKey ? 'branch-tab-link-active' : 'branch-tab-link' }}">
                {{ $tabLabel }}
            </a>
        @endforeach
    </div>

    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($summaryCards as $card)
            <div class="stat-card">
                <div class="section-title">{{ $card['title'] }}</div>
                <div class="metric-value">
                    @if ($card['isCurrency'])
                        R$ {{ number_format((float) $card['value'], 2, ',', '.') }}
                    @else
                        {{ $card['value'] }}
                    @endif
                </div>
                <p class="mt-2 text-sm leading-5 text-slate-600">{{ $card['context'] }}</p>
                @if ($card['detail'])
                    <p class="mt-1 text-xs text-slate-500">{{ $card['detail'] }}</p>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-6">
        @include("branches.partials.$tab")
    </div>
</x-app-layout>
