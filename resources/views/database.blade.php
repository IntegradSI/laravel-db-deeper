@extends('db-deeper::layouts.panel', [
    'activePage' => 'database',
    'pageTitle' => 'Banco e tabelas',
    'liveRefreshUrl' => $liveRefreshUrl,
    'refreshInterval' => $refreshInterval,
])

@section('content')
    <section class="mb-6 flex flex-col gap-3 border-b border-base-300 pb-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="mb-2 text-sm font-semibold text-primary">Inventário físico</p>
            <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Banco e tabelas</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-base-content/65">Metadados do driver Laravel, sem contagem completa de linhas nem alterações no banco.</p>
        </div>
        <span class="badge badge-outline">{{ $stats['driver_label'] }}</span>
    </section>

    <div id="database-live-content" data-live-content aria-live="off">
        @include('db-deeper::partials.database-live')
    </div>
@endsection
