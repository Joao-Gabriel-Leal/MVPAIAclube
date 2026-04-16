<x-guest-layout>
    @if ($user)
        <div class="space-y-6">
            @include('profile.partials.membership-card', ['user' => $user])

            <section class="panel p-6 sm:p-8">
                <div class="section-title">Validacao publica</div>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">Carteirinha validada</h2>
                <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600">
                    Esta consulta confirma os dados principais da carteirinha no momento da leitura do QR code.
                </p>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="panel-muted p-4">
                        <div class="section-title">Nome</div>
                        <div class="mt-3 text-lg font-bold text-slate-950">{{ $user->name }}</div>
                    </div>
                    <div class="panel-muted p-4">
                        <div class="section-title">Tipo</div>
                        <div class="mt-3 text-lg font-bold text-slate-950">{{ $user->role->label() }}</div>
                    </div>
                    <div class="panel-muted p-4">
                        <div class="section-title">Numero da carteirinha</div>
                        <div class="mt-3 text-lg font-bold text-slate-950">{{ $user->formatted_card_number }}</div>
                    </div>
                    <div class="panel-muted p-4">
                        <div class="section-title">Situacao atual</div>
                        <div class="mt-3 text-lg font-bold text-slate-950">{{ $user->card_status_label ?: 'Nao informada' }}</div>
                    </div>
                    <div class="panel-muted p-4">
                        <div class="section-title">Filial</div>
                        <div class="mt-3 text-lg font-bold text-slate-950">
                            {{ $user->isMember() ? ($user->member?->primaryBranch?->name ?: 'Nao vinculada') : ($user->dependent?->branch?->name ?: 'Nao vinculada') }}
                        </div>
                    </div>
                    <div class="panel-muted p-4">
                        <div class="section-title">Validado em</div>
                        <div class="mt-3 text-lg font-bold text-slate-950">{{ $validatedAt->format('d/m/Y H:i:s') }}</div>
                    </div>
                </div>
            </section>
        </div>
    @else
        <section class="panel p-6 sm:p-8">
            <div class="section-title">Validacao publica</div>
            <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">Carteirinha nao encontrada</h2>
            <p class="mt-3 text-sm leading-7 text-slate-600">
                O token informado nao corresponde a uma carteirinha valida. Confira o QR code e tente novamente.
            </p>
            <a href="{{ route('home') }}" class="btn-primary mt-6">Voltar para a pagina inicial</a>
        </section>
    @endif
</x-guest-layout>
