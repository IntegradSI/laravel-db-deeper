<?php

use Illuminate\Support\Facades\Route;
use Integrad\DbDeeper\Http\Controllers\DashboardController;

if (! config('db-deeper.dashboard.enabled', true)) {
    return;
}

Route::middleware(config('db-deeper.dashboard.middleware', ['web', 'auth', 'can:viewDbDeeper']))
    ->prefix(config('db-deeper.dashboard.path', 'db-deeper'))
    ->name('db-deeper.')
    ->group(function (): void {
        Route::get('/', DashboardController::class)->name('index');
    });
