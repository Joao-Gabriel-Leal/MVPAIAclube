@php
    $showHeading = $showHeading ?? true;
    $publicMode = $publicMode ?? false;
    $showValidationLink = $showValidationLink ?? (! $publicMode);
    $validatedAt = $validatedAt ?? null;
    $isMember = $user->isMember();
    $branchName = $isMember
        ? $user->member?->primaryBranch?->name
        : $user->dependent?->branch?->name;
    $supportLabel = $isMember ? 'Plano' : 'Titular';
    $supportValue = $isMember
        ? ($user->member?->plan?->name ?: 'Sem plano')
        : ($user->dependent?->member?->user?->name ?: 'Nao identificado');
    $detailLabel = $isMember ? 'Situacao' : 'Parentesco';
    $detailValue = $isMember
        ? ($user->card_status_label ?: 'Nao definido')
        : ($user->dependent?->relationship ?: '-');
    $qrSvg = (! $publicMode && $user->card_validation_url)
        ? \App\Support\CardQrCode::svg($user->card_validation_url, 168)
        : null;
    $statusClass = match ($user->card_status_tone) {
        'success' => 'membership-card-status-badge--success',
        'warning' => 'membership-card-status-badge--warning',
        'danger' => 'membership-card-status-badge--danger',
        default => 'membership-card-status-badge--info',
    };
@endphp

<section class="membership-card-stack" aria-label="Carteirinha digital">
    @if ($showHeading)
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="section-title">Carteirinha</div>
                <h2 class="mt-2 text-lg font-semibold tracking-tight text-slate-950 sm:text-[1.45rem]">Sua carteirinha</h2>
            </div>

            <div class="hidden rounded-full border border-slate-200 bg-white/80 px-3.5 py-1.5 text-[0.72rem] font-semibold tracking-[0.08em] text-slate-500 shadow-sm sm:inline-flex">
                Toque para virar
            </div>
        </div>
    @endif

    @if ($publicMode)
        <div class="membership-card-scene membership-card-scene--public" data-card-flip-root>
            <div
                class="membership-card-toggle membership-card-toggle--public"
                role="button"
                tabindex="0"
                aria-label="Toque para virar a carteirinha"
                data-card-flip-toggle
            >
                <div class="membership-card-flipper">
                    <article class="membership-card-face membership-card-face--front">
                        <div class="membership-card-face__glow"></div>

                        <div class="membership-card-face__header">
                            <div>
                                <div class="membership-card-face__eyebrow">Carteirinha digital</div>
                                <div class="membership-card-brand">{{ config('app.name') }}</div>
                            </div>

                            <div class="membership-card-type">{{ $user->role->label() }}</div>
                        </div>

                        <div class="membership-card-front__body">
                            <div class="membership-card-front__identity">
                                <div class="membership-card-face__eyebrow">{{ $isMember ? 'Associado' : 'Dependente' }}</div>
                                <h3 class="membership-card-name">{{ $user->name }}</h3>
                                <div class="membership-card-number">{{ $user->formatted_card_number }}</div>
                            </div>

                            <div class="membership-card-photo">
                                @if ($user->profile_photo_url)
                                    <img src="{{ $user->profile_photo_url }}" alt="Foto de {{ $user->name }}" class="membership-card-photo__image">
                                @else
                                    <div class="membership-card-photo__placeholder">{{ $user->profile_initials }}</div>
                                @endif
                            </div>
                        </div>

                        <div class="membership-card-meta-grid">
                            <div class="membership-card-meta">
                                <div class="membership-card-label">CPF</div>
                                <div class="membership-card-value">{{ $user->cpf ?: 'Nao informado' }}</div>
                            </div>
                            <div class="membership-card-meta">
                                <div class="membership-card-label">Filial</div>
                                <div class="membership-card-value">{{ $branchName ?: 'Nao vinculada' }}</div>
                            </div>
                            <div class="membership-card-meta">
                                <div class="membership-card-label">{{ $supportLabel }}</div>
                                <div class="membership-card-value">{{ $supportValue }}</div>
                            </div>
                            <div class="membership-card-meta">
                                <div class="membership-card-label">{{ $detailLabel }}</div>
                                <div class="membership-card-value">{{ $detailValue }}</div>
                            </div>
                        </div>

                        <div class="membership-card-front__footer">
                            <div class="membership-card-status-badge {{ $statusClass }}">
                                {{ $user->card_status_label ?: 'Nao informado' }}
                            </div>

                            <div class="membership-card-flip-pill">Ver verso</div>
                        </div>
                    </article>

                    <article class="membership-card-face membership-card-face--back">
                        <div class="membership-card-face__header">
                            <div>
                                <div class="membership-card-face__eyebrow">Leitura publica</div>
                                <div class="membership-card-brand">{{ config('app.name') }}</div>
                            </div>

                            <div class="membership-card-type membership-card-type--light">Validada</div>
                        </div>

                        <div class="membership-card-back__body membership-card-back__body--public">
                            <div class="membership-card-back__content">
                                <div>
                                    <div class="membership-card-label">Carteirinha validada</div>
                                    <p class="membership-card-instruction">
                                        Documento validado pela leitura publica.
                                    </p>
                                </div>

                                <div class="membership-card-back__meta membership-card-back__meta--public">
                                    <div class="membership-card-meta membership-card-meta--light">
                                        <div class="membership-card-label">Numero</div>
                                        <div class="membership-card-value">{{ $user->formatted_card_number }}</div>
                                    </div>
                                    <div class="membership-card-meta membership-card-meta--light">
                                        <div class="membership-card-label">Situacao</div>
                                        <div class="membership-card-value">{{ $user->card_status_label ?: 'Nao informada' }}</div>
                                    </div>
                                    <div class="membership-card-meta membership-card-meta--light">
                                        <div class="membership-card-label">Filial</div>
                                        <div class="membership-card-value">{{ $branchName ?: 'Nao vinculada' }}</div>
                                    </div>
                                    <div class="membership-card-meta membership-card-meta--light">
                                        <div class="membership-card-label">Tipo</div>
                                        <div class="membership-card-value">{{ $user->role->label() }}</div>
                                    </div>
                                </div>

                                @if ($validatedAt)
                                    <div class="membership-card-back__stamp">
                                        Validada em {{ $validatedAt->format('d/m/Y H:i:s') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </article>
                </div>
            </div>

            <div class="membership-card-flip-hint">Toque no card para virar</div>
        </div>
    @else
        <div x-data="{ flipped: false }" class="membership-card-scene" :class="{ 'is-flipped': flipped }">
            <div
                class="membership-card-toggle"
                role="button"
                tabindex="0"
                @click="flipped = !flipped"
                @keydown.enter.prevent="flipped = !flipped"
                @keydown.space.prevent="flipped = !flipped"
                x-bind:aria-label="flipped ? 'Mostrar frente da carteirinha' : 'Mostrar verso da carteirinha'"
            >
                <div class="membership-card-flipper">
                    <article class="membership-card-face membership-card-face--front">
                        <div class="membership-card-face__glow"></div>

                        <div class="membership-card-face__header">
                            <div>
                                <div class="membership-card-face__eyebrow">Carteirinha</div>
                                <div class="membership-card-brand">{{ config('app.name') }}</div>
                            </div>

                            <div class="membership-card-type">{{ $user->role->label() }}</div>
                        </div>

                        <div class="membership-card-front__body">
                            <div class="membership-card-front__identity">
                                <div class="membership-card-face__eyebrow">{{ $isMember ? 'Associado' : 'Dependente' }}</div>
                                <h3 class="membership-card-name">{{ $user->name }}</h3>
                                <div class="membership-card-number">{{ $user->formatted_card_number }}</div>
                            </div>

                            <div class="membership-card-photo">
                                @if ($user->profile_photo_url)
                                    <img src="{{ $user->profile_photo_url }}" alt="Foto de {{ $user->name }}" class="membership-card-photo__image">
                                @else
                                    <div class="membership-card-photo__placeholder">{{ $user->profile_initials }}</div>
                                @endif
                            </div>
                        </div>

                        <div class="membership-card-meta-grid">
                            <div class="membership-card-meta">
                                <div class="membership-card-label">CPF</div>
                                <div class="membership-card-value">{{ $user->cpf ?: 'Nao informado' }}</div>
                            </div>
                            <div class="membership-card-meta">
                                <div class="membership-card-label">Filial</div>
                                <div class="membership-card-value">{{ $branchName ?: 'Nao vinculada' }}</div>
                            </div>
                            <div class="membership-card-meta">
                                <div class="membership-card-label">{{ $supportLabel }}</div>
                                <div class="membership-card-value">{{ $supportValue }}</div>
                            </div>
                            <div class="membership-card-meta">
                                <div class="membership-card-label">{{ $detailLabel }}</div>
                                <div class="membership-card-value">{{ $detailValue }}</div>
                            </div>
                        </div>

                        <div class="membership-card-front__footer">
                            <div class="membership-card-status-badge {{ $statusClass }}">
                                {{ $user->card_status_label ?: 'Nao informado' }}
                            </div>

                            <div class="membership-card-flip-pill">Ver verso</div>
                        </div>
                    </article>

                    <article class="membership-card-face membership-card-face--back">
                        <div class="membership-card-strip" aria-hidden="true"></div>

                        <div class="membership-card-face__header">
                            <div>
                                <div class="membership-card-face__eyebrow">Validacao publica</div>
                                <div class="membership-card-brand">{{ config('app.name') }}</div>
                            </div>

                            <div class="membership-card-type membership-card-type--light">QR code</div>
                        </div>

                        <div class="membership-card-back__body membership-card-back__body--compact">
                            <div class="membership-card-qr-shell">
                                @if ($qrSvg)
                                    <div class="membership-card-qr" aria-label="QR code da carteirinha">{!! $qrSvg !!}</div>
                                @else
                                    <div class="membership-card-qr membership-card-qr--placeholder">
                                        QR indisponivel
                                    </div>
                                @endif
                            </div>

                            <div class="membership-card-back__content membership-card-back__content--compact">
                                <div class="membership-card-back__summary">
                                    <div class="membership-card-label">QR de validacao</div>
                                    <p class="membership-card-instruction">
                                        Escaneie para validar a carteirinha.
                                    </p>
                                </div>

                                <div class="membership-card-back__meta membership-card-back__meta--compact">
                                    <div class="membership-card-meta membership-card-meta--light">
                                        <div class="membership-card-label">Numero</div>
                                        <div class="membership-card-value">{{ $user->formatted_card_number }}</div>
                                    </div>
                                    <div class="membership-card-meta membership-card-meta--light">
                                        <div class="membership-card-label">Situacao</div>
                                        <div class="membership-card-value">{{ $user->card_status_label ?: 'Nao informada' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            </div>

            @if ($showValidationLink && $user->card_validation_url)
                <a href="{{ $user->card_validation_url }}" class="membership-card-validation-link membership-card-validation-link--external" target="_blank" rel="noreferrer">
                    Abrir validacao publica
                </a>
            @endif

            @if ($showHeading)
                <div class="membership-card-flip-hint sm:hidden">Toque no card para virar</div>
            @endif
        </div>
    @endif
</section>
