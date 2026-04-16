<x-app-layout>
    <x-slot name="header">
        <div class="max-w-4xl">
            <div class="section-title">Configuracoes do clube</div>
            <h1 class="display-title mt-3">Midia da home e carteirinha</h1>
            <p class="lead-text mt-3">
                Troque o banner principal e cada foto da galeria por aqui. Cada quadro mostra exatamente qual imagem aparece em cada parte da home.
            </p>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="status-banner-success mb-6">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('club-settings.update') }}" enctype="multipart/form-data" class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        @csrf
        @method('patch')

        <div class="space-y-6">
            <section class="panel p-6 sm:p-8">
                <div class="section-title">Carteirinha</div>
                <h2 class="mt-2 text-2xl font-semibold text-slate-950">Prefixo exibido nas identificacoes</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    Use um prefixo curto em letras e numeros, como <span class="font-semibold text-slate-900">CS</span> ou
                    <span class="font-semibold text-slate-900">CLB1</span>.
                </p>

                <div class="mt-6">
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
            </section>

            <section class="panel p-6 sm:p-8">
                <div class="section-title">Midia da home</div>
                <h2 class="mt-2 text-2xl font-semibold text-slate-950">Troque cada imagem com preview do espaco real</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    Cada slot abaixo representa um espaco exato da pagina inicial. Se a proporcao ou o tamanho nao baterem com o recomendado, o sistema bloqueia o envio.
                </p>

                <div class="mt-6 space-y-5">
                    @foreach ($mediaSlots as $slot => $definition)
                        @php($asset = $homeMediaLibrary[$slot] ?? null)

                        <article class="media-slot-card">
                            <div class="media-slot-card__header">
                                <div>
                                    <div class="section-title">{{ $definition['title'] }}</div>
                                    <h3 class="mt-2 text-xl font-semibold text-slate-950">{{ $definition['description'] }}</h3>
                                </div>

                                <span class="chip-brand">
                                    {{ $asset ? 'Imagem cadastrada' : 'Usando placeholder' }}
                                </span>
                            </div>

                            <div class="mt-5 grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(16rem,22rem)]">
                                <div>
                                    <div class="media-slot-preview {{ $slot === 'hero_banner' ? 'media-slot-preview--hero' : '' }}">
                                        @if ($asset)
                                            <img src="{{ $asset->url() }}" alt="{{ $definition['title'] }}" class="media-slot-preview__image">
                                        @else
                                            <div class="media-slot-preview__placeholder">
                                                <span>{{ $definition['placeholder_label'] }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div class="media-slot-specs">
                                        <div>
                                            <span class="summary-label">Proporcao</span>
                                            <div class="summary-value">{{ \App\Support\ClubMediaSlots::ratioLabel($definition) }}</div>
                                        </div>
                                        <div>
                                            <span class="summary-label">Tamanho sugerido</span>
                                            <div class="summary-value">{{ $definition['recommended_size'] }}</div>
                                        </div>
                                        <div>
                                            <span class="summary-label">Minimo aceito</span>
                                            <div class="summary-value">{{ $definition['min_width'] }} x {{ $definition['min_height'] }} px</div>
                                        </div>
                                        <div>
                                            <span class="summary-label">Formatos</span>
                                            <div class="summary-value">{{ $definition['formats'] }}</div>
                                        </div>
                                    </div>

                                    <div>
                                        <label for="{{ $slot }}" class="field-label">Substituir imagem</label>
                                        <input
                                            id="{{ $slot }}"
                                            name="{{ $slot }}"
                                            type="file"
                                            accept=".jpg,.jpeg,.png,.webp"
                                            class="field-input file:mr-4 file:rounded-full file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:font-semibold file:text-slate-700 hover:file:bg-slate-200"
                                        >
                                        <p class="field-hint">Envie somente a imagem deste espaco. O sistema valida proporcao, formato e tamanho.</p>
                                        <x-input-error class="mt-2" :messages="$errors->get($slot)" />
                                    </div>

                                    @if ($asset)
                                        <label class="inline-flex items-center gap-3 text-sm font-medium text-slate-600">
                                            <input type="checkbox" name="remove_slots[]" value="{{ $slot }}" class="rounded border-slate-300 text-slate-900 focus:ring-slate-300">
                                            Remover a imagem atual deste espaco
                                        </label>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <div class="flex items-center gap-4">
                <button type="submit" class="btn-primary">Salvar configuracoes</button>
            </div>
        </div>

        <aside class="space-y-6">
            <section class="panel-dark p-6 sm:p-8">
                <div class="section-title !text-white/70">Como ler esta tela</div>
                <h2 class="mt-3 text-2xl font-semibold text-white">Cada quadro mostra um ponto real da home</h2>
                <div class="mt-5 space-y-4 text-sm leading-7 text-slate-200/85">
                    <p>O banner principal ocupa a primeira dobra inteira.</p>
                    <p>A galeria destaque abre a secao de fotos com maior impacto visual.</p>
                    <p>As demais galerias completam o grid com imagens menores, mantendo o mesmo padrao visual.</p>
                </div>
            </section>

            <section class="panel p-6 sm:p-8">
                <div class="section-title">Preview da carteirinha</div>
                <div class="mt-5">
                    <article class="membership-card-face membership-card-face--front">
                        <div class="membership-card-face__glow"></div>

                        <div class="membership-card-face__header">
                            <div>
                                <div class="membership-card-face__eyebrow">Preview</div>
                                <div class="membership-card-brand">ClubeAIA</div>
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
                                <div class="membership-card-value">O prefixo salvo aqui aparece em todas as carteirinhas do sistema.</div>
                            </div>

                            <div class="membership-card-status-badge membership-card-status-badge--success">
                                Ativo
                            </div>
                        </div>
                    </article>
                </div>
            </section>
        </aside>
    </form>
</x-app-layout>
