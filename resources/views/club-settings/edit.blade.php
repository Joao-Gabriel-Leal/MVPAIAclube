<x-app-layout>
    <x-slot name="header">
        <div class="max-w-4xl">
            <div class="section-title">Configuracoes do clube</div>
            <h1 class="display-title mt-3">Conteudo, branding e experiencia publica</h1>
            <p class="lead-text mt-3">
                Centralize aqui a identidade visual, os textos da landing, a pagina de adesao, o login e os contatos publicos da rede.
            </p>
        </div>
    </x-slot>

    @php($selectedRecommendedPlanId = old('recommended_plan_id', $clubSetting->recommended_plan_id))

    @if ($uploadRuntimeWarning)
        <div class="status-banner-warning mb-6">
            O servidor atual aceita apenas {{ $uploadRuntimeWarning['upload_limit'] }} por arquivo e {{ $uploadRuntimeWarning['post_limit'] }} por envio.
            Reinicie o ambiente local com o fluxo atualizado para liberar imagens de ate 15 MB.
        </div>
    @endif

    <form method="POST" action="{{ route('club-settings.update') }}" enctype="multipart/form-data" class="grid gap-6 xl:grid-cols-[1.18fr_0.82fr]">
        @csrf
        @method('patch')

        <div class="space-y-6">
            <section class="panel p-6 sm:p-8">
                <div class="section-title">Marca</div>
                <h2 class="mt-2 text-2xl font-semibold text-slate-950">Identidade principal do sistema</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    Esses campos definem nome, narrativa, logo, cores e numeracao da carteirinha em todas as areas da plataforma.
                </p>

                <div class="mt-6 grid gap-5">
                    <div>
                        <label for="brand_name" class="field-label">Nome da marca</label>
                        <input id="brand_name" name="brand_name" type="text" value="{{ old('brand_name', $clubSetting->brand_name) }}" class="field-input" maxlength="80" required>
                        <x-input-error class="mt-2" :messages="$errors->get('brand_name')" />
                    </div>

                    <div>
                        <label for="hero_title" class="field-label">Titulo principal da home</label>
                        <input id="hero_title" name="hero_title" type="text" value="{{ old('hero_title', $clubSetting->hero_title) }}" class="field-input" maxlength="120" required>
                        <x-input-error class="mt-2" :messages="$errors->get('hero_title')" />
                    </div>

                    <div>
                        <label for="hero_subtitle" class="field-label">Subtitulo principal da home</label>
                        <textarea id="hero_subtitle" name="hero_subtitle" class="field-textarea" maxlength="400" required>{{ old('hero_subtitle', $clubSetting->hero_subtitle) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('hero_subtitle')" />
                    </div>

                    <div>
                        <label for="about_text" class="field-label">Texto institucional</label>
                        <textarea id="about_text" name="about_text" class="field-textarea" maxlength="1200" required>{{ old('about_text', $clubSetting->about_text) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('about_text')" />
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label for="primary_color" class="field-label">Cor primaria</label>
                            <input id="primary_color" name="primary_color" type="text" value="{{ old('primary_color', $clubSetting->primary_color) }}" class="field-input uppercase" placeholder="#2446A8" required>
                            <x-input-error class="mt-2" :messages="$errors->get('primary_color')" />
                        </div>
                        <div>
                            <label for="secondary_color" class="field-label">Cor secundaria</label>
                            <input id="secondary_color" name="secondary_color" type="text" value="{{ old('secondary_color', $clubSetting->secondary_color) }}" class="field-input uppercase" placeholder="#1B2F72" required>
                            <x-input-error class="mt-2" :messages="$errors->get('secondary_color')" />
                        </div>
                        <div>
                            <label for="accent_color" class="field-label">Cor de destaque</label>
                            <input id="accent_color" name="accent_color" type="text" value="{{ old('accent_color', $clubSetting->accent_color) }}" class="field-input uppercase" placeholder="#F7D117" required>
                            <x-input-error class="mt-2" :messages="$errors->get('accent_color')" />
                        </div>
                    </div>
                </div>
            </section>

            <section class="panel p-6 sm:p-8">
                <div class="section-title">Logo e carteirinha</div>
                <h2 class="mt-2 text-2xl font-semibold text-slate-950">Aplicacao da marca no portal</h2>

                <div class="mt-6 grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(16rem,22rem)]">
                    <div class="space-y-5">
                        <div>
                            <label for="card_prefix" class="field-label">Prefixo da carteirinha</label>
                            <input
                                id="card_prefix"
                                name="card_prefix"
                                type="text"
                                maxlength="6"
                                value="{{ old('card_prefix', $clubSetting->card_prefix) }}"
                                class="field-input uppercase"
                                placeholder="AABB"
                                required
                            >
                            <p class="field-hint">Somente letras e numeros, com ate 6 caracteres.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('card_prefix')" />
                        </div>

                        <div>
                            <label for="logo" class="field-label">Substituir logo principal</label>
                            <input
                                id="logo"
                                name="logo"
                                type="file"
                                accept=".jpg,.jpeg,.png,.webp,.svg"
                                data-max-upload-bytes="6291456"
                                class="field-input file:mr-4 file:rounded-full file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:font-semibold file:text-slate-700 hover:file:bg-slate-200"
                            >
                            <p class="field-hint">Aceita JPG, PNG, WEBP ou SVG. O logo aparece no cabecalho, login e landing.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('logo')" />
                        </div>

                        @if ($clubSetting->logoMedia)
                            <label class="inline-flex items-center gap-3 text-sm font-medium text-slate-600">
                                <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-300 text-slate-900 focus:ring-slate-300">
                                Remover o logo atual
                            </label>
                        @endif
                    </div>

                    <div class="space-y-4">
                        <div class="media-slot-preview min-h-[14rem]">
                            @if ($clubSetting->logoMedia)
                                <img src="{{ $clubSetting->logoMedia->url() }}" alt="Logo atual" class="media-slot-preview__image object-contain p-4">
                            @else
                                <div class="media-slot-preview__placeholder">
                                    <span>Logo principal</span>
                                </div>
                            @endif
                        </div>

                        <div class="media-slot-specs">
                            <div>
                                <span class="summary-label">Marca</span>
                                <div class="summary-value">{{ old('brand_name', $clubSetting->brand_name) }}</div>
                            </div>
                            <div>
                                <span class="summary-label">Prefixo</span>
                                <div class="summary-value">{{ old('card_prefix', $clubSetting->card_prefix) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="panel p-6 sm:p-8">
                <div class="section-title">Home</div>
                <h2 class="mt-2 text-2xl font-semibold text-slate-950">Titulos, vitrine comercial e destaque de plano</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    Ajuste os textos institucionais das secoes publicas e escolha qual plano deve receber o selo de destaque.
                </p>

                <div class="mt-6 grid gap-5">
                    <div>
                        <label for="home_about_title" class="field-label">Titulo da secao Sobre</label>
                        <input id="home_about_title" name="home_about_title" type="text" value="{{ old('home_about_title', $clubSetting->home_about_title) }}" class="field-input" maxlength="160">
                        <x-input-error class="mt-2" :messages="$errors->get('home_about_title')" />
                    </div>

                    <div class="grid gap-5 lg:grid-cols-2">
                        <div>
                            <label for="home_gallery_title" class="field-label">Titulo da secao Galeria</label>
                            <input id="home_gallery_title" name="home_gallery_title" type="text" value="{{ old('home_gallery_title', $clubSetting->home_gallery_title) }}" class="field-input" maxlength="160">
                            <x-input-error class="mt-2" :messages="$errors->get('home_gallery_title')" />
                        </div>
                        <div>
                            <label for="home_gallery_subtitle" class="field-label">Subtitulo da secao Galeria</label>
                            <textarea id="home_gallery_subtitle" name="home_gallery_subtitle" class="field-textarea" maxlength="300">{{ old('home_gallery_subtitle', $clubSetting->home_gallery_subtitle) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('home_gallery_subtitle')" />
                        </div>
                    </div>

                    <div class="grid gap-5 lg:grid-cols-2">
                        <div>
                            <label for="home_branches_title" class="field-label">Titulo da secao Filiais</label>
                            <input id="home_branches_title" name="home_branches_title" type="text" value="{{ old('home_branches_title', $clubSetting->home_branches_title) }}" class="field-input" maxlength="160">
                            <x-input-error class="mt-2" :messages="$errors->get('home_branches_title')" />
                        </div>
                        <div>
                            <label for="home_branches_subtitle" class="field-label">Subtitulo da secao Filiais</label>
                            <textarea id="home_branches_subtitle" name="home_branches_subtitle" class="field-textarea" maxlength="300">{{ old('home_branches_subtitle', $clubSetting->home_branches_subtitle) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('home_branches_subtitle')" />
                        </div>
                    </div>

                    <div class="grid gap-5 lg:grid-cols-2">
                        <div>
                            <label for="home_plans_title" class="field-label">Titulo da secao Planos</label>
                            <input id="home_plans_title" name="home_plans_title" type="text" value="{{ old('home_plans_title', $clubSetting->home_plans_title) }}" class="field-input" maxlength="160">
                            <x-input-error class="mt-2" :messages="$errors->get('home_plans_title')" />
                        </div>
                        <div>
                            <label for="home_plans_subtitle" class="field-label">Subtitulo da secao Planos</label>
                            <textarea id="home_plans_subtitle" name="home_plans_subtitle" class="field-textarea" maxlength="300">{{ old('home_plans_subtitle', $clubSetting->home_plans_subtitle) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('home_plans_subtitle')" />
                        </div>
                    </div>

                    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(0,0.9fr)]">
                        <div>
                            <label for="home_final_cta_title" class="field-label">Titulo do CTA final</label>
                            <input id="home_final_cta_title" name="home_final_cta_title" type="text" value="{{ old('home_final_cta_title', $clubSetting->home_final_cta_title) }}" class="field-input" maxlength="160">
                            <x-input-error class="mt-2" :messages="$errors->get('home_final_cta_title')" />
                        </div>
                        <div>
                            <label for="recommended_plan_id" class="field-label">Plano com destaque</label>
                            <select id="recommended_plan_id" name="recommended_plan_id" class="field-select">
                                <option value="">Usar fallback atual (familia)</option>
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->id }}" @selected((string) $selectedRecommendedPlanId === (string) $plan->id)>
                                        {{ $plan->name }}{{ $plan->is_active ? '' : ' - inativo' }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="field-hint">Se nenhum plano for selecionado, a home tenta destacar o plano com slug <span class="font-semibold">familia</span>.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('recommended_plan_id')" />
                        </div>
                    </div>
                </div>
            </section>

            <section class="panel p-6 sm:p-8">
                <div class="section-title">Midia da home</div>
                <h2 class="mt-2 text-2xl font-semibold text-slate-950">Troque cada imagem com preview do espaco real</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    Cada slot abaixo representa um espaco da pagina inicial. O sistema recorta automaticamente no centro para caber no layout.
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
                                    <div class="media-slot-preview {{ $slot === 'hero_banner' ? 'media-slot-preview--hero' : '' }}" data-media-slot-preview>
                                        @if ($asset)
                                            <img src="{{ $asset->url() }}" alt="{{ $definition['title'] }}" class="media-slot-preview__image">
                                        @else
                                            <div class="media-slot-preview__placeholder">
                                                <span>{{ old('brand_name', $clubSetting->brand_name) }} - {{ $definition['placeholder_label'] }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <p class="field-hint mt-3">Preview com recorte central automatico, igual ao que aparece na home.</p>
                                </div>

                                <div class="space-y-4">
                                    <div class="media-slot-specs">
                                        <div>
                                            <span class="summary-label">Formato base</span>
                                            <div class="summary-value">{{ \App\Support\ClubMediaSlots::ratioLabel($definition) }}</div>
                                        </div>
                                        <div>
                                            <span class="summary-label">Tamanho ideal</span>
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
                                            data-media-slot-input
                                            data-max-upload-bytes="15728640"
                                            class="field-input file:mr-4 file:rounded-full file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:font-semibold file:text-slate-700 hover:file:bg-slate-200"
                                        >
                                        <p class="field-hint">Pode enviar fotos em formatos variados. O sistema enquadra a imagem automaticamente no centro deste slot.</p>
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

            <section class="panel p-6 sm:p-8">
                <div class="section-title">Login e adesao</div>
                <h2 class="mt-2 text-2xl font-semibold text-slate-950">Textos do acesso e da jornada publica</h2>

                <div class="mt-6 grid gap-5">
                    <div>
                        <label for="login_title" class="field-label">Titulo do login</label>
                        <input id="login_title" name="login_title" type="text" value="{{ old('login_title', $clubSetting->login_title) }}" class="field-input" maxlength="120">
                        <x-input-error class="mt-2" :messages="$errors->get('login_title')" />
                    </div>

                    <div>
                        <label for="login_subtitle" class="field-label">Subtitulo do login</label>
                        <textarea id="login_subtitle" name="login_subtitle" class="field-textarea" maxlength="300">{{ old('login_subtitle', $clubSetting->login_subtitle) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('login_subtitle')" />
                    </div>

                    <div>
                        <label for="enrollment_intro" class="field-label">Texto de abertura da adesao</label>
                        <textarea id="enrollment_intro" name="enrollment_intro" class="field-textarea" maxlength="400">{{ old('enrollment_intro', $clubSetting->enrollment_intro) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('enrollment_intro')" />
                    </div>

                    <div>
                        <label for="enrollment_notice" class="field-label">Aviso da adesao</label>
                        <textarea id="enrollment_notice" name="enrollment_notice" class="field-textarea" maxlength="400">{{ old('enrollment_notice', $clubSetting->enrollment_notice) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('enrollment_notice')" />
                    </div>
                </div>
            </section>

            <section class="panel p-6 sm:p-8">
                <div class="section-title">Contato e redes</div>
                <h2 class="mt-2 text-2xl font-semibold text-slate-950">Canais globais exibidos na landing</h2>

                <div class="mt-6 grid gap-5 lg:grid-cols-2">
                    <div>
                        <label for="site_email" class="field-label">E-mail principal</label>
                        <input id="site_email" name="site_email" type="email" value="{{ old('site_email', $clubSetting->site_email) }}" class="field-input" maxlength="255">
                        <x-input-error class="mt-2" :messages="$errors->get('site_email')" />
                    </div>

                    <div>
                        <label for="site_phone" class="field-label">Telefone principal</label>
                        <input id="site_phone" name="site_phone" type="text" value="{{ old('site_phone', $clubSetting->site_phone) }}" class="field-input" maxlength="30">
                        <x-input-error class="mt-2" :messages="$errors->get('site_phone')" />
                    </div>

                    <div>
                        <label for="site_whatsapp" class="field-label">WhatsApp</label>
                        <input id="site_whatsapp" name="site_whatsapp" type="text" value="{{ old('site_whatsapp', $clubSetting->site_whatsapp) }}" class="field-input" maxlength="30">
                        <x-input-error class="mt-2" :messages="$errors->get('site_whatsapp')" />
                    </div>

                    <div>
                        <label for="instagram_url" class="field-label">URL do Instagram</label>
                        <input id="instagram_url" name="instagram_url" type="url" value="{{ old('instagram_url', $clubSetting->instagram_url) }}" class="field-input" maxlength="255" placeholder="https://instagram.com/sua-rede">
                        <x-input-error class="mt-2" :messages="$errors->get('instagram_url')" />
                    </div>

                    <div class="lg:col-span-2">
                        <label for="facebook_url" class="field-label">URL do Facebook</label>
                        <input id="facebook_url" name="facebook_url" type="url" value="{{ old('facebook_url', $clubSetting->facebook_url) }}" class="field-input" maxlength="255" placeholder="https://facebook.com/sua-rede">
                        <x-input-error class="mt-2" :messages="$errors->get('facebook_url')" />
                    </div>
                </div>
            </section>

            <section class="panel p-6 sm:p-8">
                <div class="section-title">SEO</div>
                <h2 class="mt-2 text-2xl font-semibold text-slate-950">Titulo e descricao da pagina inicial</h2>

                <div class="mt-6 grid gap-5">
                    <div>
                        <label for="seo_title" class="field-label">Titulo SEO</label>
                        <input id="seo_title" name="seo_title" type="text" value="{{ old('seo_title', $clubSetting->seo_title) }}" class="field-input" maxlength="80">
                        <x-input-error class="mt-2" :messages="$errors->get('seo_title')" />
                    </div>

                    <div>
                        <label for="seo_description" class="field-label">Descricao SEO</label>
                        <textarea id="seo_description" name="seo_description" class="field-textarea" maxlength="160">{{ old('seo_description', $clubSetting->seo_description) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('seo_description')" />
                    </div>
                </div>
            </section>

            <div class="flex items-center gap-4">
                <button type="submit" class="btn-primary">Salvar configuracoes</button>
            </div>
        </div>

        <aside class="space-y-6">
            <section class="panel p-6 sm:p-8">
                <div class="section-title">Preview da carteirinha</div>
                <div class="mt-5">
                    <article class="membership-card-face membership-card-face--front">
                        <div class="membership-card-face__glow"></div>

                        <div class="membership-card-face__header">
                            <div>
                                <div class="membership-card-face__eyebrow">Preview</div>
                                <div class="membership-card-brand">{{ old('brand_name', $clubSetting->brand_name) }}</div>
                            </div>

                            <div class="membership-card-type">Associado</div>
                        </div>

                        <div class="membership-card-front__body">
                            <div class="membership-card-front__identity">
                                <div class="membership-card-face__eyebrow">Associado</div>
                                <h3 class="membership-card-name">Associado Exemplo</h3>
                                <div class="membership-card-number">{{ old('card_prefix', $clubSetting->card_prefix) }}-A1B2C3</div>
                            </div>

                            <div class="membership-card-photo">
                                @if ($clubSetting->logoMedia)
                                    <img src="{{ $clubSetting->logoMedia->url() }}" alt="Logo preview" class="membership-card-photo__image object-contain bg-white p-3">
                                @else
                                    <div class="membership-card-photo__placeholder">AA</div>
                                @endif
                            </div>
                        </div>

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

                        <div class="membership-card-front__footer">
                            <div class="membership-card-status-badge membership-card-status-badge--success">
                                Ativo
                            </div>

                            <div class="membership-card-flip-pill">Preview</div>
                        </div>
                    </article>
                </div>
            </section>

            <section class="panel p-6 sm:p-8">
                <div class="section-title">Resumo publico</div>
                <div class="mt-5 space-y-4 text-sm leading-7 text-slate-600">
                    <div>
                        <div class="summary-label">Login</div>
                        <div class="summary-value">{{ old('login_title', $clubSetting->login_title) ?: $clubSetting->resolvedLoginTitle() }}</div>
                    </div>
                    <div>
                        <div class="summary-label">CTA final</div>
                        <div class="summary-value">{{ old('home_final_cta_title', $clubSetting->home_final_cta_title) ?: $clubSetting->resolvedHomeFinalCtaTitle() }}</div>
                    </div>
                    <div>
                        <div class="summary-label">Contato global</div>
                        <div class="summary-value">
                            {{ old('site_email', $clubSetting->site_email) ?: 'Sem e-mail' }}
                            @if (old('site_phone', $clubSetting->site_phone))
                                - {{ old('site_phone', $clubSetting->site_phone) }}
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        </aside>
    </form>
</x-app-layout>
