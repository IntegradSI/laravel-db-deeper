<?php

use Illuminate\Support\Facades\Route;

it('registers the dashboard behind authentication and an explicit ability', function () {
    $route = Route::getRoutes()->getByName('db-deeper.index');

    expect($route)->not->toBeNull()
        ->and($route->gatherMiddleware())->toContain('auth', 'can:viewDbDeeper');
});

it('renders the dashboard with backup status and links to backup management', function () {
    $this->withoutMiddleware()
        ->get('/db-deeper')
        ->assertOk()
        ->assertSee('Saúde e atividade')
        ->assertSee('Rotinas, execuções e arquivos ficam em')
        ->assertSee('Restauração ainda não está disponível.');
});
