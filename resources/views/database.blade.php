@extends('db-deeper::layouts.panel', [
    'activePage' => 'database',
    'pageTitle' => 'Banco e tabelas',
    'liveRefreshUrl' => $liveRefreshUrl,
    'refreshInterval' => $refreshInterval,
])

@section('content')
    <section class="mb-6 flex flex-col gap-4 border-b border-base-300 pb-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-3 sm:gap-4">
            <span class="mt-1 flex size-11 shrink-0 items-center justify-center rounded-field border border-base-300 bg-base-100 text-base-content/70 sm:size-12">
                <i data-lucide="database" class="size-5" aria-hidden="true"></i>
            </span>
            <div>
                <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">Banco e tabelas</h1>
                <p class="mt-1 max-w-3xl text-sm leading-6 text-base-content/65">Capacidade, estrutura e espaço ocupado pela conexão Laravel. A coleta lê metadados e não altera os dados do banco.</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2 sm:justify-end">
            <span class="badge badge-ghost gap-1.5"><i data-lucide="server-cog" class="size-3.5" aria-hidden="true"></i>{{ $stats['driver_label'] }}</span>
            <span class="badge badge-outline max-w-full break-all font-mono text-xs">{{ $stats['connection_name'] }}</span>
        </div>
    </section>

    <div id="database-live-content" data-live-content aria-live="off">
        @include('db-deeper::partials.database-live')
    </div>
@endsection
