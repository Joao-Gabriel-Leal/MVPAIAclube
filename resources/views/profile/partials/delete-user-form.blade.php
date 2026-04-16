<section class="space-y-6">
    <header>
        <div class="section-title">Zona sensivel</div>
        <h2 class="mt-2 text-2xl font-semibold text-slate-950">Excluir conta</h2>
        <p class="mt-3 text-sm leading-7 text-slate-600">
            Esta acao remove permanentemente a conta e os dados relacionados. Use apenas quando realmente quiser encerrar o acesso.
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Excluir conta</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 sm:p-8">
            @csrf
            @method('delete')

            <div class="section-title">Confirmacao final</div>
            <h2 class="mt-2 text-2xl font-semibold text-slate-950">
                Tem certeza de que deseja excluir sua conta?
            </h2>

            <p class="mt-3 text-sm leading-7 text-slate-600">
                Para confirmar a exclusao definitiva, informe sua senha atual.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="Senha" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full"
                    placeholder="Senha"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Cancelar
                </x-secondary-button>

                <x-danger-button>
                    Excluir conta
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
