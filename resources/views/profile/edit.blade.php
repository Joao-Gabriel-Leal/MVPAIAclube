<x-app-layout>
    @php
        $usesTabbedProfile = $user->isCardHolder();
        $activeProfileTab = $errors->updatePassword->isNotEmpty() || session('status') === 'password-updated'
            ? 'security'
            : 'profile';
    @endphp

    <x-slot name="header">
        <div class="max-w-3xl">
            <div class="section-title">Conta</div>
            <h1 class="display-title mt-3">Perfil</h1>
        </div>
    </x-slot>

    <div class="{{ $usesTabbedProfile ? 'space-y-6' : 'space-y-5' }}">
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
                        <h2 class="mt-2 text-xl font-semibold text-slate-950">Ajustes</h2>
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
                    </div>
                </div>

                <div class="profile-tab-panel" x-cloak x-show="activeTab === 'profile'">
                    @include('profile.partials.update-profile-information-form')
                </div>

                <div class="profile-tab-panel" x-cloak x-show="activeTab === 'security'">
                    @include('profile.partials.update-password-form')
                </div>
            </section>
        @else
            <div class="panel p-5 sm:p-6">
                <div class="max-w-2xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="panel p-5 sm:p-6">
                <div class="max-w-2xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="panel p-5 sm:p-6">
                <div class="max-w-2xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
