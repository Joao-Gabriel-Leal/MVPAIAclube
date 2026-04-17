<x-guest-layout>
    @php($clubSetting = \App\Models\ClubSetting::current())

    <div class="mb-6">
        <div class="section-title">Portal de acesso</div>
        <h1 class="mt-2.5 text-[1.75rem] font-semibold tracking-tight text-slate-950 sm:text-[1.85rem]">{{ $clubSetting->resolvedLoginTitle() }}</h1>
        <p class="lead-text mt-3">{{ $clubSetting->resolvedLoginSubtitle() }}</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-[1.125rem]">
        @csrf

        <div>
            <label for="email" class="field-label">E-mail</label>
            <input id="email" class="field-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <label for="password" class="field-label">Senha</label>
            <input id="password" class="field-input" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <label class="inline-flex items-center gap-2.5 text-[0.88rem] font-medium text-slate-600">
            <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-slate-900 focus:ring-slate-300" name="remember">
            Manter conectado
        </label>

        <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('home') }}" class="btn-secondary">Voltar para a pagina inicial</a>
            <button class="btn-primary" type="submit">Entrar</button>
        </div>
    </form>

    @if (app()->environment('local'))
        <div class="panel-muted auth-access-card mt-6 p-3.5 text-[0.88rem] text-slate-600">
            <div class="font-semibold text-slate-900">Acessos locais</div>
            <div class="mt-2.5 space-y-1 text-[0.68rem] leading-5 text-slate-500">
                <p>Admin Matriz: admin.matriz@clube.test / password</p>
                <p>Admin Filial: admin.brasilia@clube.test / password</p>
                <p>Associado: associado@clube.test / password</p>
                <p>Dependente: dependente@clube.test / password</p>
            </div>
        </div>
    @endif
</x-guest-layout>
