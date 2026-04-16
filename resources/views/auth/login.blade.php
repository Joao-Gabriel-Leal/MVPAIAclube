<x-guest-layout>
    <div class="mb-8">
        <div class="section-title">Portal de acesso</div>
        <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">Entrar no ClubeAIA</h1>
        <p class="mt-3 text-sm leading-7 text-slate-600">
            Use seu e-mail e senha para acessar a rotina da administracao, dos associados e dos dependentes.
        </p>
    </div>

    <x-auth-session-status class="mb-5" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
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

        <label class="inline-flex items-center gap-3 text-sm font-medium text-slate-600">
            <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-slate-900 focus:ring-slate-300" name="remember">
            Manter conectado
        </label>

        <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('home') }}" class="inline-link">Voltar para a pagina inicial</a>
            <button class="btn-primary" type="submit">Entrar</button>
        </div>
    </form>

    @if (app()->environment('local'))
        <div class="panel-muted mt-8 p-4 text-sm text-slate-600">
            <div class="font-semibold text-slate-900">Acesso de teste no ambiente local</div>
            <p class="mt-2 leading-6">Administracao, associados e dependentes continuam usando o mesmo login do ambiente de desenvolvimento.</p>
            <div class="mt-3 space-y-1.5 text-xs leading-6 text-slate-500">
                <p>Admin Matriz: admin.matriz@clube.test / password</p>
                <p>Admin Filial: admin.zonasul@clube.test / password</p>
                <p>Associado: associado@clube.test / password</p>
                <p>Dependente: dependente@clube.test / password</p>
            </div>
        </div>
    @endif
</x-guest-layout>
