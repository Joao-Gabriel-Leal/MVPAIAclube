@php
    $isMember = $user->isMember();
    $branchName = $isMember
        ? $user->member?->primaryBranch?->name
        : $user->dependent?->branch?->name;
    $supportLabel = $isMember ? 'Plano' : 'Titular';
    $supportValue = $isMember
        ? ($user->member?->plan?->name ?: 'Sem plano vinculado')
        : ($user->dependent?->member?->user?->name ?: 'Titular nao identificado');
    $detailLabel = $isMember ? 'Situacao' : 'Parentesco';
    $detailValue = $isMember
        ? ($user->card_status_label ?: 'Nao definido')
        : ($user->dependent?->relationship ?: '-');
    $qrSvg = $user->card_validation_url ? \App\Support\CardQrCode::svg($user->card_validation_url, 172) : null;
    $statusClass = match ($user->card_status_tone) {
        'success' => 'membership-card-status-badge--success',
        'warning' => 'membership-card-status-badge--warning',
        'danger' => 'membership-card-status-badge--danger',
        default => 'membership-card-status-badge--info',
    };
@endphp

<section class="membership-card-stack" aria-label="Carteirinha digital">
    <div class="flex items-start justify-between gap-4">
        <div>
            <div class="section-title">Carteirinha digital</div>
            <h2 class="mt-3 text-2xl font-semibold tracking-tight text-slate-950 sm:text-3xl">Sua identificacao do clube, pronta para usar</h2>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Frente e verso digitais com foto, dados principais e um QR code unico para validacao publica.
            </p>
        </div>

        <div class="hidden rounded-full border border-slate-200 bg-white/80 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.28em] text-slate-500 shadow-sm sm:inline-flex">
            Cartao fisico no digital
        </div>
    </div>

    <div class="membership-card-grid">
        <article class="membership-card-face membership-card-face--front">
            <div class="membership-card-face__glow"></div>

            <div class="membership-card-face__header">
                <div>
                    <div class="membership-card-face__eyebrow">Frente digital</div>
                    <div class="membership-card-brand">{{ config('app.name') }}</div>
                </div>

                <div class="membership-card-type">{{ $user->role->label() }}</div>
            </div>

            <div class="membership-card-front__body">
                <div class="membership-card-chip" aria-hidden="true"></div>

                <div class="membership-card-photo">
                    @if ($user->profile_photo_url)
                        <img src="{{ $user->profile_photo_url }}" alt="Foto de {{ $user->name }}" class="membership-card-photo__image">
                    @else
                        <div class="membership-card-photo__placeholder">{{ $user->profile_initials }}</div>
                    @endif
                </div>

                <div class="membership-card-front__identity">
                    <div class="membership-card-face__eyebrow">Titular da carteirinha</div>
                    <h3 class="membership-card-name">{{ $user->name }}</h3>
                    <div class="membership-card-number">{{ $user->formatted_card_number }}</div>

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
                </div>
            </div>

            <div class="membership-card-front__footer">
                <div>
                    <div class="membership-card-label">Uso pessoal</div>
                    <div class="membership-card-value">Apresente este cartao digital no clube sempre que precisar validar seu vinculo.</div>
                </div>

                <div class="membership-card-status-badge {{ $statusClass }}">
                    {{ $user->card_status_label ?: 'Nao informado' }}
                </div>
            </div>
        </article>

        <article class="membership-card-face membership-card-face--back">
            <div class="membership-card-strip" aria-hidden="true"></div>

            <div class="membership-card-face__header">
                <div>
                    <div class="membership-card-face__eyebrow">Verso digital</div>
                    <div class="membership-card-brand">{{ config('app.name') }}</div>
                </div>

                <div class="membership-card-type membership-card-type--light">Validacao publica</div>
            </div>

            <div class="membership-card-back__body">
                <div class="membership-card-qr-shell">
                    @if ($qrSvg)
                        <div class="membership-card-qr" aria-label="QR code da carteirinha">{!! $qrSvg !!}</div>
                    @else
                        <div class="membership-card-qr membership-card-qr--placeholder">
                            QR indisponivel
                        </div>
                    @endif
                </div>

                <div class="membership-card-back__content">
                    <div>
                        <div class="membership-card-label">Valide esta carteirinha</div>
                        <p class="membership-card-instruction">
                            Escaneie o QR code para abrir a pagina publica de validacao e conferir a situacao atual desta carteirinha.
                        </p>
                    </div>

                    <div class="membership-card-back__meta">
                        <div class="membership-card-meta membership-card-meta--light">
                            <div class="membership-card-label">Numero</div>
                            <div class="membership-card-value">{{ $user->formatted_card_number }}</div>
                        </div>
                        <div class="membership-card-meta membership-card-meta--light">
                            <div class="membership-card-label">Situacao</div>
                            <div class="membership-card-value">{{ $user->card_status_label ?: 'Nao informada' }}</div>
                        </div>
                    </div>

                    @if ($user->card_validation_url)
                        <a href="{{ $user->card_validation_url }}" class="membership-card-validation-link" target="_blank" rel="noreferrer">
                            Abrir validacao publica
                        </a>
                    @endif
                </div>
            </div>
        </article>
    </div>
</section>
