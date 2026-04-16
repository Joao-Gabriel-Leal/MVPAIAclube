<x-app-layout>
    @php
        $usesTabbedProfile = $user->isCardHolder();
        $activeProfileTab = $errors->userDeletion->isNotEmpty()
            ? 'account'
            : ($errors->updatePassword->isNotEmpty() || session('status') === 'password-updated'
                ? 'security'
                : 'profile');
    @endphp

    <x-slot name="header">
        <div class="max-w-3xl">
            <div class="section-title">Conta</div>
            <h1 class="display-title mt-3">Perfil</h1>
            <p class="lead-text mt-3">
                {{ $usesTabbedProfile
                    ? 'Acesse sua carteirinha e organize sua conta por secoes mais simples de usar.'
                    : 'Atualize seus dados, senha e seguranca em uma area mais organizada.' }}
            </p>
        </div>
    </x-slot>

    <div class="{{ $usesTabbedProfile ? 'space-y-8' : 'space-y-6' }}">
        @if ($usesTabbedProfile && $user->formatted_card_number)
            @include('profile.partials.membership-card', ['user' => $user])
        @endif

        @if ($usesTabbedProfile)
            <section
                x-data="{ activeTab: @js($activeProfileTab) }"
                data-profile-layout="tabbed"
                data-active-profile-tab="{{ $activeProfileTab }}"
                class="profile-shell"
            >
                <div class="profile-shell__header">
                    <div>
                        <div class="section-title">Minha conta</div>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-950">Tudo separado por sessoes</h2>
                        <p class="mt-3 text-sm leading-7 text-slate-600">
                            Escolha a secao que voce quer alterar sem ficar com o perfil inteiro aberto de uma vez.
                        </p>
                    </div>

                    <div class="profile-tabs" role="tablist" aria-label="Sessoes do perfil">
                        <button
                            type="button"
                            role="tab"
                            class="profile-tab"
                            x-bind:class="{ 'is-active': activeTab === 'profile' }"
                            x-bind:aria-selected="activeTab === 'profile'"
                            x-on:click="activeTab = 'profile'"
                        >
                            Dados
                        </button>
                        <button
                            type="button"
                            role="tab"
                            class="profile-tab"
                            x-bind:class="{ 'is-active': activeTab === 'security' }"
                            x-bind:aria-selected="activeTab === 'security'"
                            x-on:click="activeTab = 'security'"
                        >
                            Seguranca
                        </button>
                        <button
                            type="button"
                            role="tab"
                            class="profile-tab"
                            x-bind:class="{ 'is-active': activeTab === 'account' }"
                            x-bind:aria-selected="activeTab === 'account'"
                            x-on:click="activeTab = 'account'"
                        >
                            Conta
                        </button>
                    </div>
                </div>

                <div class="profile-tab-panel" x-cloak x-show="activeTab === 'profile'">
                    @include('profile.partials.update-profile-information-form')
                </div>

                <div class="profile-tab-panel" x-cloak x-show="activeTab === 'security'">
                    @include('profile.partials.update-password-form')
                </div>

                <div class="profile-tab-panel" x-cloak x-show="activeTab === 'account'">
                    @include('profile.partials.delete-user-form')
                </div>
            </section>
        @else
            <div class="panel p-6 sm:p-8">
                <div class="max-w-2xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="panel p-6 sm:p-8">
                <div class="max-w-2xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="panel p-6 sm:p-8">
                <div class="max-w-2xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
