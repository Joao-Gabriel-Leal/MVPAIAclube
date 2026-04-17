<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Comprovante {{ $invoice->billing_period->format('m-Y') }}</title>
        @vite(['resources/css/app.css'])
    </head>
    <body class="bg-slate-50">
        <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('member-invoices.show', $invoice) }}" class="btn-secondary">Voltar</a>
                <button type="button" class="btn-primary" onclick="window.print()">Imprimir comprovante</button>
            </div>

            <section class="panel p-8">
                <div class="section-title">Comprovante de pagamento</div>
                <h1 class="mt-2 text-3xl font-semibold text-slate-950">{{ $invoice->branch->name }}</h1>
                <p class="mt-3 text-sm leading-6 text-slate-600">Mensalidade quitada manualmente no sistema do clube.</p>

                <div class="mt-8 grid gap-4 md:grid-cols-2">
                    <div class="panel-muted p-5">
                        <div class="font-semibold text-slate-900">Titular</div>
                        <div class="mt-2 text-sm text-slate-600">{{ $invoice->member->user->name }}</div>
                    </div>
                    <div class="panel-muted p-5">
                        <div class="font-semibold text-slate-900">Competencia</div>
                        <div class="mt-2 text-sm text-slate-600">{{ $invoice->billing_period->format('m/Y') }}</div>
                    </div>
                    <div class="panel-muted p-5">
                        <div class="font-semibold text-slate-900">Valor pago</div>
                        <div class="mt-2 text-sm text-slate-600">R$ {{ number_format((float) ($invoice->paid_amount ?? $invoice->amount), 2, ',', '.') }}</div>
                    </div>
                    <div class="panel-muted p-5">
                        <div class="font-semibold text-slate-900">Data da baixa</div>
                        <div class="mt-2 text-sm text-slate-600">{{ $invoice->paid_at?->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </section>
        </div>
    </body>
</html>
