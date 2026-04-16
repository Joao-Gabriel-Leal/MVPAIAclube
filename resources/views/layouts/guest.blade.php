<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>ClubeAIA</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800|fraunces:500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="mx-auto flex min-h-screen max-w-6xl items-center px-4 py-8 sm:px-6 lg:px-8">
            <div class="grid w-full gap-6 lg:grid-cols-[1.02fr_0.98fr]">
                <div class="panel-dark overflow-hidden">
                    <div class="relative h-full p-8 sm:p-10">
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(255,255,255,0.12),_transparent_30%),radial-gradient(circle_at_bottom_right,_rgba(196,167,112,0.14),_transparent_24%)]"></div>

                        <div class="relative flex h-full flex-col">
                            <div class="flex items-center justify-between gap-3">
                                <x-application-logo theme="dark" />
                                <a href="{{ route('home') }}" class="inline-flex items-center rounded-full border border-white/20 px-4 py-2 text-sm font-semibold text-slate-100 transition hover:border-white/30 hover:bg-white/10">Pagina inicial</a>
                            </div>

                            <div class="mt-12 section-title text-slate-300">ClubeAIA</div>
                            <h1 class="mt-4 text-4xl font-semibold tracking-tight text-white sm:text-5xl">
                                Acesso unico para a rotina da administracao, dos associados e dos dependentes.
                            </h1>
                            <p class="mt-6 max-w-xl text-base leading-8 text-slate-200/90">
                                Entre no portal para acompanhar cadastros, reservas, atendimento e a operacao do clube em um ambiente mais direto e confiavel.
                            </p>

                            <div class="mt-10 grid gap-4 sm:grid-cols-2">
                                <div class="rounded-[1.7rem] border border-white/10 bg-white/10 p-5 backdrop-blur-sm">
                                    <div class="text-sm font-bold text-white">Administracao</div>
                                    <p class="mt-2 text-sm leading-6 text-slate-200/80">Controle de unidades, membros, financeiro e recursos em uma linguagem mais institucional.</p>
                                </div>
                                <div class="rounded-[1.7rem] border border-white/10 bg-white/10 p-5 backdrop-blur-sm">
                                    <div class="text-sm font-bold text-white">Acesso dos membros</div>
                                    <p class="mt-2 text-sm leading-6 text-slate-200/80">Associados e dependentes entram no mesmo portal para consultar e seguir a rotina do clube.</p>
                                </div>
                            </div>

                            <div class="mt-auto pt-10 text-sm leading-7 text-slate-300/80">
                                O acesso permanece o mesmo. Este espaco agora abre o sistema com uma leitura mais proxima da identidade de um clube real.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel px-6 py-8 sm:px-8 sm:py-10">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
