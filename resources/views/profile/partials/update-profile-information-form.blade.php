<section>
    <header>
        <div class="section-title">Informacoes da conta</div>
        <h2 class="mt-2 text-2xl font-semibold text-slate-950">Dados principais</h2>
        <p class="mt-3 text-sm leading-7 text-slate-600">
            Atualize nome e e-mail sem depender de fluxos extras de verificacao que nao fazem parte deste sistema.
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <label for="profile_photo" class="field-label">Foto da carteirinha</label>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <div class="h-20 w-20 overflow-hidden rounded-[1.6rem] border border-violet-100 bg-violet-50">
                    @if ($user->profile_photo_url)
                        <img src="{{ $user->profile_photo_url }}" alt="Foto atual de {{ $user->name }}" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full w-full items-center justify-center bg-[linear-gradient(135deg,_rgba(124,58,237,0.14),_rgba(244,114,182,0.18))] text-lg font-extrabold text-violet-700">
                            {{ $user->profile_initials }}
                        </div>
                    @endif
                </div>

                <div class="flex-1">
                    <input id="profile_photo" name="profile_photo" type="file" accept="image/*" class="field-input file:mr-4 file:rounded-full file:border-0 file:bg-violet-100 file:px-4 file:py-2 file:font-semibold file:text-violet-700 hover:file:bg-violet-200">
                    <p class="field-hint">Envie uma foto nitida para aparecer na carteirinha digital. A imagem fica armazenada no banco do sistema.</p>
                </div>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />
        </div>

        <div>
            <x-input-label for="name" value="Nome" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Salvar alteracoes</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-medium text-slate-500"
                >Salvo.</p>
            @endif
        </div>
    </form>
</section>
