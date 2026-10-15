<?php

use Illuminate\Support\Facades\Route;
use Integrad\LaravelDatabaseBackup\Http\Controllers\DashboardController;

if (! config('database-backup.dashboard.enabled', true)) {
    return;
}

Route::middleware(config('database-backup.dashboard.middleware', ['web', 'auth', 'can:viewDatabaseBackups']))
    ->prefix(config('database-backup.dashboard.path', 'database-backup'))
    ->name('database-backup.')
    ->group(function (): void {
        Route::get('/', DashboardController::class)->name('index');
    });
