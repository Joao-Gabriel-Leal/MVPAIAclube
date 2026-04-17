@php($isCardHolder = $user->isCardHolder())
@php($photoLabel = $isCardHolder ? 'Foto da carteirinha' : 'Foto do perfil')
@php($photoHint = $isCardHolder
    ? 'Envie uma foto nitida para aparecer na carteirinha digital. A imagem fica armazenada no banco do sistema.'
    : 'Envie uma imagem para personalizar sua conta no sistema.')

<section>
    <header>
        <div class="section-title">Informacoes da conta</div>
        <h2 class="mt-2 text-xl font-semibold text-slate-950">{{ $isCardHolder ? 'Dados permitidos' : 'Dados principais' }}</h2>
    </header>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-5 space-y-5">
        @csrf
        @method('patch')

        <div>
            <label for="profile_photo" class="field-label">{{ $photoLabel }}</label>
            <div class="flex flex-col gap-3.5 sm:flex-row sm:items-center">
                <div class="h-[4.5rem] w-[4.5rem] overflow-hidden rounded-[1.35rem] border border-amber-100 bg-amber-50/80">
                    @if ($user->profile_photo_url)
                        <img src="{{ $user->profile_photo_url }}" alt="Foto atual de {{ $user->name }}" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full w-full items-center justify-center bg-[linear-gradient(135deg,_rgba(242,207,47,0.28),_rgba(41,88,184,0.16))] text-[0.95rem] font-extrabold text-[rgb(var(--club-brand))]">
                            {{ $user->profile_initials }}
                        </div>
                    @endif
                </div>

                <div class="flex-1">
                    <input id="profile_photo" name="profile_photo" type="file" accept="image/*" class="field-input file:mr-3 file:rounded-full file:border-0 file:bg-amber-100 file:px-3.5 file:py-1.5 file:font-semibold file:text-[rgb(var(--club-brand))] hover:file:bg-amber-200">
                    <p class="field-hint">{{ $photoHint }}</p>
                </div>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />
        </div>

        @if (! $isCardHolder)
            <div>
                <x-input-label for="name" value="Nome" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>
        @endif

        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        @if ($isCardHolder)
            <div>
                <x-input-label for="phone" value="Telefone" />
                <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $user->phone)" autocomplete="tel" />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>
        @endif

        <div class="flex items-center gap-3">
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
