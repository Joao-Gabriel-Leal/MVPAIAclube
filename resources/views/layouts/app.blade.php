<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @php($clubSetting = \App\Models\ClubSetting::current())
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $clubSetting->resolvedBrandName() }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>:root { {{ $clubSetting->themeCssVariablesInline() }} }</style>
    </head>
    <body x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false">
        <div class="relative min-h-screen overflow-hidden">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-80 bg-[radial-gradient(circle_at_top,_rgba(41,88,184,0.14),_transparent_55%)]"></div>
            <div class="pointer-events-none absolute inset-x-0 top-24 h-72 bg-[radial-gradient(circle_at_center,_rgba(242,207,47,0.2),_transparent_55%)]"></div>

            <div class="app-shell relative z-10">
                <aside class="hidden lg:block">
                    @include('layouts.navigation')
                </aside>

                <div class="flex min-w-0 flex-1 flex-col">
                    <div class="mobile-topbar lg:hidden">
                        <button
                            type="button"
                            class="mobile-topbar__menu"
                            @click="sidebarOpen = true"
                            aria-controls="mobile-sidebar"
                            x-bind:aria-expanded="sidebarOpen.toString()"
                        >
                            <span class="sr-only">Abrir menu</span>
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 7h16"></path>
                                <path d="M4 12h16"></path>
                                <path d="M4 17h16"></path>
                            </svg>
                        </button>

                        <div class="min-w-0">
                            <div class="mobile-topbar__eyebrow">Area logada</div>
                            <div class="mobile-topbar__title">{{ $clubSetting->resolvedBrandName() }}</div>
                        </div>
                    </div>

                    <main class="mx-auto w-full max-w-[108rem] flex-1 px-4 pb-10 pt-5 sm:px-5 lg:px-6 lg:pt-6">
                        @isset($header)
                            <div class="panel mb-5 overflow-hidden px-5 py-6 sm:px-6 sm:py-6">
                                <div class="absolute inset-y-0 right-0 hidden w-56 bg-[radial-gradient(circle,_rgba(242,207,47,0.18),_transparent_62%)] lg:block"></div>
                                <div class="relative">{{ $header }}</div>
                            </div>
                        @endisset

                        @if (session('status'))
                            <div class="status-banner status-banner-success mb-5">
                                {{ session('status') }}
                            </div>
                        @endif

                        {{ $slot }}
                    </main>
                </div>
            </div>

            <div
                x-cloak
                x-show="sidebarOpen"
                x-transition.opacity
                class="mobile-sidebar-overlay lg:hidden"
                @click="sidebarOpen = false"
                aria-hidden="true"
            ></div>

            <aside
                id="mobile-sidebar"
                x-cloak
                x-show="sidebarOpen"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="-translate-x-full opacity-0"
                x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="translate-x-0 opacity-100"
                x-transition:leave-end="-translate-x-full opacity-0"
                class="mobile-sidebar lg:hidden"
            >
                @include('layouts.navigation', ['mobile' => true])
            </aside>
        </div>
    </body>
</html>
