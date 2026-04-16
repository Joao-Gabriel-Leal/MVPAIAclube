<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ClubResourceController;
use App\Http\Controllers\ClubSettingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DependentController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MediaAssetController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PublicEnrollmentController;
use App\Http\Controllers\PublicMembershipCardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/midia/{mediaAsset}', [MediaAssetController::class, 'show'])->name('media.show');
Route::get('/adesao/{branch:slug}', [PublicEnrollmentController::class, 'create'])->name('enrollment.create');
Route::post('/adesao/{branch:slug}', [PublicEnrollmentController::class, 'store'])->name('enrollment.store');
Route::get('/carteirinhas/{token}', [PublicMembershipCardController::class, 'show'])->name('cards.show');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/perfil', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile', [ProfileController::class, 'edit']);
    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::delete('/profile', [ProfileController::class, 'destroy']);

    Route::resource('membros', MemberController::class)
        ->parameters(['membros' => 'member'])
        ->except(['destroy']);
    Route::post('membros/{member}/status', [MemberController::class, 'updateStatus'])->name('members.status');

    Route::resource('dependentes', DependentController::class)
        ->parameters(['dependentes' => 'dependent'])
        ->except(['destroy']);
    Route::post('dependentes/{dependent}/status', [DependentController::class, 'updateStatus'])->name('dependents.status');

    Route::resource('reservas', ReservationController::class)
        ->parameters(['reservas' => 'reservation'])
        ->only(['index', 'create', 'store']);
    Route::post('reservas/{reservation}/status', [ReservationController::class, 'updateStatus'])->name('reservations.status');

    Route::middleware('role:admin_matrix,admin_branch')->group(function () {
        Route::resource('recursos', ClubResourceController::class)
            ->parameters(['recursos' => 'club_resource'])
            ->except(['destroy', 'show']);

        Route::get('financeiro', [FinanceController::class, 'index'])->name('finance.index');
        Route::post('financeiro/gerar', [FinanceController::class, 'generate'])->name('finance.generate');
        Route::post('financeiro/faturas/{membership_invoice}/baixar', [FinanceController::class, 'markPaid'])->name('finance.mark-paid');

        Route::get('relatorios', [ReportController::class, 'index'])->name('reports.index');

        Route::get('planos', [PlanController::class, 'index'])->name('plans.index');
        Route::get('planos/novo', [PlanController::class, 'create'])->name('plans.create');
        Route::post('planos', [PlanController::class, 'store'])->name('plans.store');
        Route::get('planos/{plan}/editar', [PlanController::class, 'edit'])->name('plans.edit');
        Route::put('planos/{plan}', [PlanController::class, 'update'])->name('plans.update');
        Route::delete('planos/{plan}', [PlanController::class, 'destroy'])->name('plans.destroy');
    });

    Route::middleware('role:admin_matrix')->group(function () {
        Route::resource('filiais', BranchController::class)
            ->parameters(['filiais' => 'branch'])
            ->except(['show', 'destroy']);

        Route::get('configuracoes/carteirinha', [ClubSettingController::class, 'edit'])->name('club-settings.edit');
        Route::patch('configuracoes/carteirinha', [ClubSettingController::class, 'update'])->name('club-settings.update');

        Route::get('usuarios-filiais', [AdminUserController::class, 'index'])->name('admin-users.index');
        Route::post('usuarios-filiais', [AdminUserController::class, 'store'])->name('admin-users.store');
    });

    Route::middleware('role:admin_matrix,admin_branch')->group(function () {
        Route::get('filiais/{branch}', [BranchController::class, 'show'])->name('filiais.show');
    });
});

require __DIR__.'/auth.php';
