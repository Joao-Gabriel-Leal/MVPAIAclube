<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Services\BillingService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerateMonthlyInvoicesCommand extends Command
{
    protected $signature = 'club:generate-monthly-invoices {--month=} {--branch=}';

    protected $description = 'Gera mensalidades do periodo informado para o clube.';

    public function handle(BillingService $billingService): int
    {
        $period = Carbon::parse(($this->option('month') ?: now()->format('Y-m')).'-01');
        $branch = $this->option('branch')
            ? Branch::query()->find($this->option('branch'))
            : null;

        $invoices = $billingService->generateMonthlyInvoices($period, $branch);

        $this->info(sprintf('%s mensalidades processadas para %s.', $invoices->count(), $period->format('m/Y')));

        return self::SUCCESS;
    }
}
