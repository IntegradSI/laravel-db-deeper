<?php

use Illuminate\Support\Facades\Route;
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
    });
