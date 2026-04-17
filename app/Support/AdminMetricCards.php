<?php

namespace App\Support;

use App\Models\Branch;
use Illuminate\Support\Carbon;

class AdminMetricCards
{
    public static function make(string $title, int|float|string $value, string $context, bool $isCurrency = false, ?string $detail = null): array
    {
        return [
            'title' => $title,
            'value' => $value,
            'context' => $context,
            'isCurrency' => $isCurrency,
            'detail' => $detail,
        ];
    }

    public static function currency(string $title, float $value, string $context, ?string $detail = null): array
    {
        return self::make($title, $value, $context, true, $detail);
    }

    public static function count(string $title, int|float|string $value, string $context, ?string $detail = null): array
    {
        return self::make($title, $value, $context, false, $detail);
    }

    public static function competencyContext(Carbon|string $billingPeriod, ?Branch $branch = null): string
    {
        $period = $billingPeriod instanceof Carbon
            ? $billingPeriod->copy()->startOfMonth()
            : Carbon::createFromFormat('Y-m', (string) $billingPeriod)->startOfMonth();

        return self::scopeContext('Competencia '.$period->format('m/Y'), $branch);
    }

    public static function dateRangeContext(Carbon|string $startDate, Carbon|string $endDate, ?Branch $branch = null): string
    {
        $start = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $end = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);

        return self::scopeContext(
            sprintf('Periodo %s a %s', $start->format('d/m/Y'), $end->format('d/m/Y')),
            $branch
        );
    }

    public static function scopeContext(string $label, ?Branch $branch = null): string
    {
        return trim($label).' | '.self::scopeLabel($branch);
    }

    public static function scopeLabel(?Branch $branch = null): string
    {
        return $branch?->name ?? 'Consolidado da rede';
    }

    public static function detailCount(int $count, string $label = 'registro'): string
    {
        return sprintf('%d %s(s)', $count, $label);
    }
}
