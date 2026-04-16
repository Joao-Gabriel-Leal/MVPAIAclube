<x-app-layout>
    <x-slot name="header">
        <div class="max-w-3xl">
            <div class="section-title">Configuracao global</div>
            <h1 class="display-title mt-3">Carteirinha digital</h1>
            <p class="lead-text mt-3">Defina o prefixo exibido em todas as carteirinhas. O sufixo aleatorio de cada pessoa continua fixo.</p>
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <div class="panel p-6 sm:p-8">
            <div class="section-title">Prefixo</div>
            <h2 class="mt-2 text-2xl font-semibold text-slate-950">Identificacao do clube</h2>
            <p class="mt-3 text-sm leading-7 text-slate-600">
                Use um prefixo curto em letras e numeros, como <span class="font-semibold text-slate-900">CS</span> ou
                <span class="font-semibold text-slate-900">CLB1</span>. Ao salvar, todas as carteirinhas passam a exibir o novo prefixo.
            </p>

            <form method="POST" action="{{ route('club-settings.update') }}" class="mt-6 space-y-6">
                @csrf
                @method('patch')

                <div>
                    <label for="card_prefix" class="field-label">Prefixo da carteirinha</label>
                    <input
                        id="card_prefix"
                        name="card_prefix"
                        type="text"
                        maxlength="6"
                        value="{{ old('card_prefix', $clubSetting->card_prefix) }}"
                        class="field-input uppercase"
                        placeholder="CS"
                        required
                    >
                    <p class="field-hint">Somente letras e numeros, com ate 6 caracteres.</p>
                    <x-input-error class="mt-2" :messages="$errors->get('card_prefix')" />
                </div>

                <div class="flex items-center gap-4">
                    <button type="submit" class="btn-primary">Salvar prefixo</button>
                </div>
            </form>
        </div>

        <div class="panel-dark p-6 sm:p-8">
            <div class="section-title !text-violet-200">Preview</div>
            <div class="mt-5">
                <article class="membership-card-face membership-card-face--front">
                    <div class="membership-card-face__glow"></div>

                    <div class="membership-card-face__header">
                        <div>
                            <div class="membership-card-face__eyebrow">Preview</div>
                            <div class="membership-card-brand">{{ config('app.name') }}</div>
                        </div>

                        <div class="membership-card-type">Associado</div>
                    </div>

                    <div class="membership-card-front__body">
                        <div class="membership-card-chip" aria-hidden="true"></div>

                        <div class="membership-card-photo">
                            <div class="membership-card-photo__placeholder">AE</div>
                        </div>

                        <div class="membership-card-front__identity">
                            <div class="membership-card-face__eyebrow">Titular da carteirinha</div>
                            <h3 class="membership-card-name">Associado Exemplo</h3>
                            <div class="membership-card-number">{{ old('card_prefix', $clubSetting->card_prefix) }}-A1B2C3</div>

                            <div class="membership-card-meta-grid">
                                <div class="membership-card-meta">
                                    <div class="membership-card-label">Tipo</div>
                                    <div class="membership-card-value">Associado</div>
                                </div>
                                <div class="membership-card-meta">
                                    <div class="membership-card-label">Status</div>
                                    <div class="membership-card-value">Ativo</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="membership-card-front__footer">
                        <div>
                            <div class="membership-card-label">Visual</div>
                            <div class="membership-card-value">Este preview acompanha o estilo da carteirinha exibida para associados e dependentes.</div>
                        </div>

                        <div class="membership-card-status-badge membership-card-status-badge--success">
                            Ativo
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </div>
</x-app-layout>
