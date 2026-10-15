@extends('db-deeper::layouts.panel', [
    'activePage' => 'overview',
    'pageTitle' => 'Saúde do banco',
    'liveRefreshUrl' => $liveRefreshUrl,
    'refreshInterval' => $refreshInterval,
])

@section('content')
    <section class="mb-6 flex flex-col gap-4 border-b border-base-300 pb-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="mb-2 text-sm font-semibold text-primary">Monitoramento do banco</p>
            <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Saúde e atividade</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-base-content/65">Acompanhe a conexão da aplicação, consultas em execução e padrões que precisam de diagnóstico.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 text-sm">
            <span class="badge badge-outline font-mono">{{ $databaseConnection }} · {{ $monitoring['driver_label'] }}</span>
        </div>
    </section>

    <div id="dashboard-live-content" data-live-content aria-live="off">
        @include('db-deeper::partials.dashboard-live')
    </div>
@endsection
