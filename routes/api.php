<?php

use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\DashboardMetricsController;
use App\Http\Controllers\Api\ReportDataController;
use App\Models\Branch;
use App\Models\Plan;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('branches', fn () => Branch::query()->active()->orderBy('name')->get());
    Route::get('plans', fn () => Plan::query()->active()->orderBy('name')->get());

    Route::middleware(['web', 'auth'])->group(function () {
        Route::get('resources/{club_resource}/availability/month', [AvailabilityController::class, 'month'])->name('api.availability.month');
        Route::get('resources/{club_resource}/availability', [AvailabilityController::class, 'index'])->name('api.availability');
        Route::get('dashboard', [DashboardMetricsController::class, 'index'])->name('api.dashboard');
        Route::get('reports', [ReportDataController::class, 'index'])->name('api.reports');
    });
});
