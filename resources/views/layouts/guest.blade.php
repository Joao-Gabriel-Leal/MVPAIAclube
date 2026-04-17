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
    <body>
        <div class="mx-auto flex min-h-screen max-w-6xl items-center px-4 py-5 sm:px-6 lg:px-8">
            <div class="grid w-full gap-4 lg:grid-cols-[0.98fr_1.02fr]">
                <div class="panel-dark auth-hero-panel overflow-hidden">
                    <div class="relative h-full p-6 sm:p-7">
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(255,255,255,0.08),_transparent_18%),radial-gradient(circle_at_bottom_right,_rgba(242,207,47,0.16),_transparent_22%)]"></div>

                        <div class="relative z-10 flex h-full flex-col">
                            <div class="flex items-center gap-3">
                                <x-application-logo theme="dark" />
                            </div>

                            <div class="auth-hero-copy">
                                <div class="section-title !text-white/70">{{ $clubSetting->resolvedBrandName() }}</div>
                                <h1 class="auth-hero-title">
                                    Portal do clube
                                </h1>
                                <p class="auth-hero-text">
                                    Acesso a reservas, carteirinha e administracao.
                                </p>

                                <div class="mt-6 flex flex-wrap gap-2">
                                    <span class="auth-hero-chip">Carteirinha</span>
                                    <span class="auth-hero-chip">Reservas</span>
                                    <span class="auth-hero-chip">Gestao</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel px-5 py-6 sm:px-6 sm:py-7">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
