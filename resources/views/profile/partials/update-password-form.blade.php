<section>
    <header>
        <div class="section-title">Seguranca</div>
        <h2 class="mt-2 text-xl font-semibold text-slate-950">Atualizar senha</h2>
        <p class="mt-3 text-[0.9rem] leading-6 text-slate-600">
            Use uma senha forte para manter sua conta protegida e alinhada com a operacao atual do sistema.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-5 space-y-5">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" value="Senha atual" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" value="Nova senha" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" value="Confirmar nova senha" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-3">
            <x-primary-button>Salvar nova senha</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-medium text-slate-500"
                >Senha atualizada.</p>
            @endif
        </div>
    </form>
</section>
