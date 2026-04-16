<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Adesao {{ $branch->name }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800|fraunces:500,600,700" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <x-application-logo />
                <a href="{{ route('home') }}" class="btn-secondary">Voltar</a>
            </div>

            <div class="grid gap-8 lg:grid-cols-[0.9fr_1.1fr]">
                <div class="panel-dark overflow-hidden">
                    <div class="relative h-full p-8 sm:p-10">
                        <div class="section-title text-slate-300">Solicitacao de adesao</div>
                        <h1 class="mt-4 text-4xl font-semibold tracking-tight text-white">{{ $branch->name }}</h1>
                        <p class="mt-4 text-sm leading-7 text-slate-200/80">
                            Seu pedido sera enviado para avaliacao da administracao da unidade, mantendo um primeiro contato mais claro e institucional.
                        </p>

                        <div class="mt-8 space-y-4">
                            <div class="rounded-[1.7rem] border border-white/10 bg-white/10 p-5 text-sm text-slate-100/90">
                                <div class="text-xs font-extrabold uppercase tracking-[0.28em] text-slate-300">Endereco</div>
                                <p class="mt-3 leading-7">{{ $branch->address }}</p>
                            </div>

                            <div class="rounded-[1.7rem] border border-white/10 bg-white/10 p-5 text-sm text-slate-100/90">
                                <div class="text-xs font-extrabold uppercase tracking-[0.28em] text-slate-300">Mensalidade base</div>
                                <p class="mt-3 text-2xl font-bold text-white">R$ {{ number_format((float) $branch->monthly_fee_default, 2, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel p-8">
                    <div class="section-title">Formulario</div>
                    <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Criar conta de associado</h2>
                    <p class="lead-text mt-3">Preencha os dados principais e escolha um plano para enviar sua solicitacao de adesao.</p>

                    <form method="POST" action="{{ route('enrollment.store', $branch) }}" class="mt-8 space-y-5">
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

                        <div class="grid gap-5 sm:grid-cols-2">
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

                        <button class="btn-primary w-full" type="submit">Enviar adesao</button>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
