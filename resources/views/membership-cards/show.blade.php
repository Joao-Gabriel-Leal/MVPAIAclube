<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @php($clubSetting = \App\Models\ClubSetting::current())
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $clubSetting->resolvedBrandName() }} - Carteirinha digital</title>

        @vite(['resources/css/app.css'])
        <style>:root { {{ $clubSetting->themeCssVariablesInline() }} }</style>
    </head>
    <body class="membership-card-public-page">
        <main class="membership-card-public-shell">
            @if ($user)
                @include('profile.partials.membership-card', [
                    'user' => $user,
                    'showHeading' => false,
                    'publicMode' => true,
                    'showValidationLink' => false,
                    'validatedAt' => $validatedAt,
                ])
            @else
                <section class="panel max-w-md p-6 text-center sm:p-8">
                    <div class="section-title">Validacao publica</div>
                    <h1 class="mt-3 text-2xl font-semibold tracking-tight text-slate-950">Carteirinha nao encontrada</h1>
                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        O QR code informado nao corresponde a uma carteirinha valida.
                    </p>
                </section>
            @endif
        </main>
        <script>
            document.querySelectorAll('[data-card-flip-toggle]').forEach((toggle) => {
                const root = toggle.closest('[data-card-flip-root]');

                if (!root) {
                    return;
                }

                const flip = () => root.classList.toggle('is-flipped');

                toggle.addEventListener('click', flip);
                toggle.addEventListener('keydown', (event) => {
                    if (event.key !== 'Enter' && event.key !== ' ') {
                        return;
                    }

                    event.preventDefault();
                    flip();
                });
            });
        </script>
    </body>
</html>
