<?php

use Illuminate\Support\Facades\Route;

it('registers the dashboard behind authentication and an explicit ability', function () {
    $route = Route::getRoutes()->getByName('db-deeper.index');

    expect($route)->not->toBeNull()
        ->and($route->gatherMiddleware())->toContain('auth', 'can:viewDbDeeper');
});

it('renders the dashboard without exposing a backup action before the engine is ready', function () {
    $this->withoutMiddleware()
        ->get('/db-deeper')
        ->assertOk()
        ->assertSee('Backups do banco de dados')
        ->assertSee('O pacote ainda não executa backups');
});
