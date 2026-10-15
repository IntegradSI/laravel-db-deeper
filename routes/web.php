<?php

use Illuminate\Support\Facades\Route;
use Integrad\DbDeeper\Http\Controllers\BackupController;
use Integrad\DbDeeper\Http\Controllers\BackupKeyController;
use Integrad\DbDeeper\Http\Controllers\BackupLiveController;
use Integrad\DbDeeper\Http\Controllers\BackupManagementController;
use Integrad\DbDeeper\Http\Controllers\BackupScheduleController;
use Integrad\DbDeeper\Http\Controllers\DashboardController;
use Integrad\DbDeeper\Http\Controllers\DashboardLiveController;
use Integrad\DbDeeper\Http\Controllers\DatabaseStatsController;
use Integrad\DbDeeper\Http\Controllers\DatabaseStatsLiveController;

if (! config('db-deeper.dashboard.enabled', true)) {
    return;
}

Route::middleware(config('db-deeper.dashboard.middleware', ['web', 'auth', 'can:viewDbDeeper']))
    ->prefix(config('db-deeper.dashboard.path', 'db-deeper'))
    ->name('db-deeper.')
    ->group(function (): void {
        Route::get('/', DashboardController::class)->name('index');
        Route::get('/database', DatabaseStatsController::class)->name('database');
        Route::get('/live/overview', DashboardLiveController::class)->name('live.overview');
        Route::get('/live/database', DatabaseStatsLiveController::class)->name('live.database');
        Route::get('/backups', BackupManagementController::class)->name('backups.index');
        Route::get('/live/backups', BackupLiveController::class)->name('live.backups');
        Route::post('/backups/diagnose', [BackupController::class, 'diagnose'])->name('backups.diagnose');
        Route::post('/backups/run', [BackupController::class, 'store'])->name('backups.run');
        Route::get('/backups/{backup}/download', [BackupController::class, 'download'])->name('backups.download');
        Route::post('/backups/schedules', [BackupScheduleController::class, 'store'])->name('backups.schedules.store');
        Route::get('/backups/schedules/{schedule}/edit', [BackupScheduleController::class, 'edit'])->name('backups.schedules.edit');
        Route::patch('/backups/schedules/{schedule}', [BackupScheduleController::class, 'update'])->name('backups.schedules.update');
        Route::patch('/backups/schedules/{schedule}/toggle', [BackupScheduleController::class, 'toggle'])->name('backups.schedules.toggle');
        Route::delete('/backups/schedules/{schedule}', [BackupScheduleController::class, 'destroy'])->name('backups.schedules.destroy');
        Route::get('/backups/keys', [BackupKeyController::class, 'index'])->name('backups.keys.index');
        Route::post('/backups/keys', [BackupKeyController::class, 'store'])->name('backups.keys.store');
    });
