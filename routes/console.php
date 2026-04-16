<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:import-sqlite-to-pgsql', function () {
    $sqlitePath = env('LEGACY_SQLITE_DATABASE', database_path('database.sqlite'));

    if (! File::exists($sqlitePath)) {
        $this->error("Banco SQLite legado nao encontrado em: {$sqlitePath}");

        return self::FAILURE;
    }

    $pdo = new PDO('sqlite:'.$sqlitePath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $tables = [
        'branches',
        'plans',
        'club_settings',
        'media_assets',
        'users',
        'members',
        'member_branch_affiliations',
        'dependents',
        'club_resources',
        'plan_resource',
        'resource_schedules',
        'resource_blocks',
        'membership_invoices',
        'financial_transactions',
        'reservations',
        'audit_logs',
    ];

    foreach ($tables as $table) {
        $exists = $pdo
            ->query("SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = '{$table}'")
            ->fetchColumn();

        if (! $exists) {
            $this->warn("Tabela ausente no legado: {$table}");

            continue;
        }

        $rows = $pdo->query("SELECT * FROM {$table}")->fetchAll(PDO::FETCH_ASSOC);
        DB::connection('pgsql')->statement("TRUNCATE TABLE {$table} RESTART IDENTITY CASCADE");

        if (empty($rows)) {
            $this->line("{$table}: sem registros para importar.");

            continue;
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::connection('pgsql')->table($table)->insert($chunk);
        }

        if (collect($rows)->first()['id'] ?? false) {
            DB::connection('pgsql')->statement("
                SELECT setval(
                    pg_get_serial_sequence('{$table}', 'id'),
                    COALESCE((SELECT MAX(id) FROM {$table}), 1),
                    (SELECT COUNT(*) > 0 FROM {$table})
                )
            ");
        }

        $this->info("{$table}: ".count($rows).' registro(s) importado(s).');
    }

    return self::SUCCESS;
})->purpose('Importa os dados do SQLite legado para o PostgreSQL local');
