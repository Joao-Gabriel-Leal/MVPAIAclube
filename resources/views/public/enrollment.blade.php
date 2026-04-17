<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $clubSetting->resolvedBrandName() }} - Adesao {{ $branch->name }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>:root { {{ $clubSetting->themeCssVariablesInline() }} }</style>
    </head>
    <body>
        <div class="enrollment-shell">
            <div class="grid w-full gap-4 lg:grid-cols-[0.96fr_1.04fr]">
                <section class="panel-dark auth-hero-panel overflow-hidden">
                    <div class="relative h-full p-6 sm:p-7">
                        <div class="relative z-10 flex h-full flex-col">
                            <div class="flex items-start justify-between gap-4">
                                <x-application-logo theme="dark" />
                                <a href="{{ route('home') }}" class="btn-secondary border-white/25 bg-white/10 text-white hover:border-white/35 hover:bg-white/15">Voltar</a>
                            </div>

                            <div class="mt-8">
                                <div class="section-title !text-white/70">Solicitacao de adesao</div>
                                <h1 class="mt-3 text-[2rem] font-semibold tracking-tight text-white sm:text-[2.3rem]">{{ $branch->name }}</h1>
                                <p class="mt-3 max-w-xl text-[0.96rem] leading-7 text-white/82">
                                    {{ $clubSetting->resolvedEnrollmentIntro() }}
                                </p>
                            </div>

                            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                                <div class="rounded-[1.45rem] border border-white/16 bg-white/10 p-4 backdrop-blur-sm">
                                    <div class="section-title !text-white/70">Endereco</div>
                                    <p class="mt-2.5 text-sm leading-6 text-white/92">{{ $branch->address ?: 'Endereco nao informado.' }}</p>
                                </div>

                                <div class="rounded-[1.45rem] border border-white/16 bg-white/10 p-4 backdrop-blur-sm">
                                    <div class="section-title !text-white/70">Mensalidade base</div>
                                    <p class="mt-2.5 text-[1.75rem] font-bold text-white">R$ {{ number_format((float) $branch->monthly_fee_default, 2, ',', '.') }}</p>
                                </div>

                                @if ($branch->publicSummary() !== '')
                                    <div class="rounded-[1.45rem] border border-white/16 bg-white/10 p-4 backdrop-blur-sm sm:col-span-2">
                                        <div class="section-title !text-white/70">Resumo da unidade</div>
                                        <p class="mt-2.5 text-sm leading-6 text-white/92">{{ $branch->publicSummary() }}</p>
                                    </div>
                                @endif

                                @if ($branch->publicPhone() || $branch->publicWhatsapp() || $branch->publicHours() || $branch->email)
                                    <div class="rounded-[1.45rem] border border-white/16 bg-white/10 p-4 backdrop-blur-sm sm:col-span-2">
                                        <div class="section-title !text-white/70">Contato da unidade</div>
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @if ($branch->publicPhone())
                                                <a href="{{ $branch->publicPhoneLink() }}" class="chip-brand">{{ $branch->publicPhone() }}</a>
                                            @endif
                                            @if ($branch->publicWhatsapp())
                                                <a href="{{ $branch->publicWhatsappLink() }}" class="chip-info" target="_blank" rel="noreferrer">WhatsApp {{ $branch->publicWhatsapp() }}</a>
                                            @endif
                                            @if ($branch->publicHours())
                                                <span class="chip-info">{{ $branch->publicHours() }}</span>
                                            @endif
                                            @if ($branch->email)
                                                <a href="mailto:{{ $branch->email }}" class="chip-info">{{ $branch->email }}</a>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="enrollment-steps">
                                <div class="enrollment-step">
                                    <strong class="font-semibold text-white">1.</strong> Voce envia seus dados e escolhe o plano ideal para a unidade.
                                </div>
                                <div class="enrollment-step">
                                    <strong class="font-semibold text-white">2.</strong> A solicitacao vai para <span class="font-semibold text-white">Propostas</span>, sem entrar direto em Associados.
                                </div>
                                <div class="enrollment-step">
                                    <strong class="font-semibold text-white">3.</strong> A filial aprova ou reprova o cadastro antes da liberacao final.
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="panel px-5 py-6 sm:px-6 sm:py-7">
                    <div class="flex flex-col gap-4 border-b border-slate-200/80 pb-5 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <div class="section-title">Formulario</div>
                            <h2 class="mt-2 text-[1.7rem] font-semibold tracking-tight text-slate-950">Criar conta de associado</h2>
                            <p class="lead-text mt-3">Preencha os dados principais e escolha um plano para enviar sua solicitacao de adesao.</p>
                        </div>

                        <div class="rounded-[1.1rem] border border-amber-100/80 bg-amber-50/80 px-4 py-3 text-sm text-slate-700">
                            <div class="font-semibold text-slate-900">Destino do cadastro</div>
                            <div class="mt-1">Fila de propostas da filial</div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('enrollment.store', $branch) }}" class="mt-6 space-y-[1.125rem]">
                        @csrf

                        <div>
                            <label class="field-label" for="plan_id">Plano</label>
                            <select id="plan_id" name="plan_id" class="field-select">
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>{{ $plan->name }} - R$ {{ number_format((float) $plan->base_price, 2, ',', '.') }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('plan_id')" class="mt-2" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="field-label" for="name">Nome completo</label>
                                <input class="field-input" id="name" name="name" value="{{ old('name') }}" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div>
                                <label class="field-label" for="cpf">CPF</label>
                                <input class="field-input" id="cpf" name="cpf" value="{{ old('cpf') }}" required />
                                <x-input-error :messages="$errors->get('cpf')" class="mt-2" />
                            </div>

                            <div>
                                <label class="field-label" for="birth_date">Data de nascimento</label>
                                <input class="field-input" id="birth_date" type="date" name="birth_date" value="{{ old('birth_date') }}" required />
                                <x-input-error :messages="$errors->get('birth_date')" class="mt-2" />
                            </div>

                            <div>
                                <label class="field-label" for="email">E-mail</label>
                                <input class="field-input" id="email" type="email" name="email" value="{{ old('email') }}" required />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <div>
                                <label class="field-label" for="phone">Telefone</label>
                                <input class="field-input" id="phone" name="phone" value="{{ old('phone') }}" required />
                                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                            </div>

                            <div>
                                <label class="field-label" for="password">Senha</label>
                                <input class="field-input" id="password" type="password" name="password" required />
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>

                            <div>
                                <label class="field-label" for="password_confirmation">Confirmar senha</label>
                                <input class="field-input" id="password_confirmation" type="password" name="password_confirmation" required />
                            </div>
                        </div>

                        <div class="rounded-[1.25rem] border border-slate-200 bg-slate-50/80 px-4 py-3 text-sm leading-6 text-slate-600">
                            {{ $clubSetting->resolvedEnrollmentNotice() }}
                        </div>

                        <button class="btn-primary w-full" type="submit">Enviar adesao</button>
                    </form>
                </section>
            </div>
        </div>
    </body>
</html>
